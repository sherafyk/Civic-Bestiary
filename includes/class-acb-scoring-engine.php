<?php

if (!defined('ABSPATH')) {
    exit;
}

final class ACB_Scoring_Engine
{
    public function score(array $answers, $total_active_questions = 0, array $settings = array())
    {
        $dimensions = ACB_Core_Data::dimensions();
        $animals = ACB_Core_Data::animals();
        $houses = ACB_Core_Data::houses();
        $subfactors = ACB_Core_Data::updown_subfactors();
        $settings = wp_parse_args($settings, ACB_Core_Data::settings_defaults());
        $minimum_questions = max(1, (int) $settings['minimum_questions']);

        $total_answered = count($answers);
        $dimension_totals = array_fill_keys(array_keys($dimensions), 0.0);
        $animal_totals = array_fill_keys(array_keys($animals), 0.0);
        $updown_totals = array_fill_keys(array_keys($subfactors), 0.0);

        foreach ($answers as $answer) {
            $this->add_map($dimension_totals, $answer['dimension_delta'] ?? array());
            $this->add_map($animal_totals, $answer['animal_delta'] ?? array());
            $this->add_map($updown_totals, $answer['updown_delta'] ?? array());
        }

        $dimension_scores = $this->dimension_scores($dimension_totals, $dimensions, $total_answered);
        $updown = $this->updown_scores($updown_totals, $subfactors, $total_answered);
        $matches = $this->animal_matches($dimension_scores, $animal_totals, $animals, $total_answered);
        $house = $this->house_result($matches, $animals, $houses);

        $primary = $matches[0] ?? array();
        $secondary = $matches[1] ?? array();
        $gap = isset($matches[0], $matches[1]) ? (float) $matches[0]['similarity'] - (float) $matches[1]['similarity'] : 0.0;
        $confidence_label = $this->confidence_label($total_answered, $minimum_questions, $gap);
        $completion_percent = $total_active_questions > 0 ? min(100, ($total_answered / (int) $total_active_questions) * 100) : 0;

        return array(
            'dimensions' => array_values($dimension_scores),
            'dimensions_by_key' => $dimension_scores,
            'animals' => array(
                'primary' => $primary,
                'secondary' => $secondary,
                'matches' => $matches,
                'blend_type' => $this->blend_type($primary, $secondary, $animals),
            ),
            'house' => $house,
            'updown' => $updown,
            'meta' => array(
                'engine_version' => ACB_VERSION,
                'total_answered' => $total_answered,
                'total_active_questions' => (int) $total_active_questions,
                'completion_percent' => round($completion_percent, 2),
                'minimum_questions' => $minimum_questions,
                'minimum_met' => $total_answered >= $minimum_questions,
                'confidence_label' => $confidence_label,
                'top_match_gap' => round($gap, 2),
                'scored_at' => current_time('mysql'),
            ),
        );
    }

    private function dimension_scores(array $totals, array $definitions, $total_answered)
    {
        $scores = array();
        foreach ($definitions as $key => $definition) {
            $raw = (float) ($totals[$key] ?? 0);
            $average = $total_answered > 0 ? $raw / $total_answered : 0.0;
            $score = $this->clamp(50 + ($average * 25), 0, 100);
            $scores[$key] = array(
                'key' => $key,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'score' => round($score, 2),
                'raw_total' => round($raw, 4),
                'average_signal' => round($average, 4),
                'band' => $this->band($score),
            );
        }

        return $scores;
    }

    private function updown_scores(array $totals, array $definitions, $total_answered)
    {
        $scores = array();
        $sum = 0.0;
        foreach ($definitions as $key => $label) {
            $raw = (float) ($totals[$key] ?? 0);
            $average = $total_answered > 0 ? $raw / $total_answered : 0.0;
            $score = $this->clamp(50 + ($average * 25), 0, 100);
            $sum += $score;
            $scores[$key] = array(
                'key' => $key,
                'label' => $label,
                'score' => round($score, 2),
                'raw_total' => round($raw, 4),
                'average_signal' => round($average, 4),
                'band' => $this->band($score),
            );
        }

        $index = count($scores) ? $sum / count($scores) : 50;

        return array(
            'index' => round($index, 2),
            'band' => $this->updown_band($index),
            'subfactors' => array_values($scores),
        );
    }

    private function animal_matches(array $dimension_scores, array $animal_totals, array $animals, $total_answered)
    {
        $user_vector = array();
        foreach ($dimension_scores as $key => $score) {
            $user_vector[$key] = (float) $score['score'];
        }

        $matches = array();
        foreach ($animals as $key => $animal) {
            $centroid_similarity = $this->centered_cosine_similarity($user_vector, $animal['centroids']);
            $direct_average = $total_answered > 0 ? ((float) ($animal_totals[$key] ?? 0) / $total_answered) : 0.0;
            $direct_boost = $this->clamp($direct_average * 8, -18, 18);
            $similarity = $this->clamp($centroid_similarity + $direct_boost, 0, 100);
            $match = $animal;
            $match['similarity'] = round($similarity, 2);
            $match['centroid_similarity'] = round($centroid_similarity, 2);
            $match['direct_signal'] = round($direct_average, 4);
            $matches[] = $match;
        }

        usort($matches, function ($a, $b) {
            if ($b['similarity'] !== $a['similarity']) {
                return $b['similarity'] <=> $a['similarity'];
            }

            return strcmp($a['key'], $b['key']);
        });

        foreach ($matches as $index => $match) {
            $matches[$index]['rank'] = $index + 1;
        }

        return $matches;
    }

    private function house_result(array $matches, array $animals, array $houses)
    {
        $scores = array();
        $counts = array();
        foreach ($matches as $match) {
            $house_key = $animals[$match['key']]['house'] ?? '';
            if (!$house_key) {
                continue;
            }
            if (!isset($scores[$house_key])) {
                $scores[$house_key] = 0.0;
                $counts[$house_key] = 0;
            }
            $scores[$house_key] += (float) $match['similarity'];
            $counts[$house_key]++;
        }

        $house_matches = array();
        foreach ($houses as $key => $house) {
            $score = !empty($counts[$key]) ? $scores[$key] / $counts[$key] : 0;
            $house_matches[] = array(
                'key' => $key,
                'label' => $house['label'],
                'instinct' => $house['instinct'],
                'question' => $house['question'],
                'score' => round($score, 2),
            );
        }

        usort($house_matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $primary = $house_matches[0] ?? array('key' => '', 'label' => '', 'score' => 0);
        $primary['matches'] = $house_matches;

        return $primary;
    }

    private function centered_cosine_similarity(array $user_vector, array $centroid)
    {
        $dot = 0.0;
        $user_norm = 0.0;
        $centroid_norm = 0.0;

        foreach (array_keys(ACB_Core_Data::dimensions()) as $dimension) {
            $u = ((float) ($user_vector[$dimension] ?? 50)) - 50.0;
            $c = ((float) ($centroid[$dimension] ?? 50)) - 50.0;
            $dot += $u * $c;
            $user_norm += $u * $u;
            $centroid_norm += $c * $c;
        }

        if ($user_norm <= 0 || $centroid_norm <= 0) {
            return 50.0;
        }

        $cosine = $dot / (sqrt($user_norm) * sqrt($centroid_norm));
        return $this->clamp((($cosine + 1) / 2) * 100, 0, 100);
    }

    private function confidence_label($total_answered, $minimum_questions, $gap)
    {
        if ($total_answered < $minimum_questions) {
            return 'calibrating';
        }

        if ($gap < 3) {
            return 'mixed';
        }

        if ($total_answered >= 25 && $gap >= 7) {
            return 'high';
        }

        return 'medium';
    }

    private function blend_type(array $primary, array $secondary, array $animals)
    {
        $primary_key = $primary['key'] ?? '';
        $secondary_key = $secondary['key'] ?? '';
        if (!$primary_key || !$secondary_key || !isset($animals[$primary_key], $animals[$secondary_key])) {
            return array(
                'key' => 'unformed',
                'label' => __('Still forming', 'american-civic-bestiary'),
            );
        }

        if (($animals[$primary_key]['house'] ?? '') === ($animals[$secondary_key]['house'] ?? '')) {
            return array(
                'key' => 'same_house',
                'label' => __('Same-house blend', 'american-civic-bestiary'),
            );
        }

        if (in_array($secondary_key, $animals[$primary_key]['tensions'] ?? array(), true)) {
            return array(
                'key' => 'tension_blend',
                'label' => __('Tension blend', 'american-civic-bestiary'),
            );
        }

        return array(
            'key' => 'cross_house_bridge',
            'label' => __('Cross-house bridge', 'american-civic-bestiary'),
        );
    }

    private function band($score)
    {
        $score = (float) $score;
        if ($score >= 75) {
            return array('key' => 'defining', 'label' => __('Defining', 'american-civic-bestiary'));
        }
        if ($score >= 60) {
            return array('key' => 'strong', 'label' => __('Strong', 'american-civic-bestiary'));
        }
        if ($score >= 41) {
            return array('key' => 'balanced', 'label' => __('Balanced', 'american-civic-bestiary'));
        }
        if ($score >= 26) {
            return array('key' => 'low', 'label' => __('Lower emphasis', 'american-civic-bestiary'));
        }

        return array('key' => 'very_low', 'label' => __('Very low emphasis', 'american-civic-bestiary'));
    }

    private function updown_band($score)
    {
        $score = (float) $score;
        if ($score >= 75) {
            return array('key' => 'high_capture_literacy', 'label' => __('High capture literacy', 'american-civic-bestiary'));
        }
        if ($score >= 60) {
            return array('key' => 'developed_capture_literacy', 'label' => __('Developed capture literacy', 'american-civic-bestiary'));
        }
        if ($score >= 41) {
            return array('key' => 'mixed_capture_literacy', 'label' => __('Mixed capture literacy', 'american-civic-bestiary'));
        }

        return array('key' => 'low_capture_literacy', 'label' => __('Low capture-literacy signal', 'american-civic-bestiary'));
    }

    private function add_map(array &$target, array $source)
    {
        foreach ($source as $key => $value) {
            if (!isset($target[$key]) || !is_numeric($value)) {
                continue;
            }
            $target[$key] += (float) $value;
        }
    }

    private function clamp($value, $min, $max)
    {
        return max($min, min($max, (float) $value));
    }
}
