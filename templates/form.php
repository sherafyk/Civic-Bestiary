<?php if (!defined('ABSPATH')) { exit; } ?>
<form class="acb-form acb-form--<?php echo esc_attr($flow_stage ?? 'initial'); ?>" method="post">
    <div class="acb-form__header">
        <?php if (!empty($settings['assessment_eyebrow'])) : ?>
            <p class="acb-eyebrow"><?php echo esc_html($settings['assessment_eyebrow']); ?></p>
        <?php endif; ?>
        <h2><?php echo esc_html('refine' === ($flow_stage ?? '') ? __('Refinement scenarios', 'american-civic-bestiary') : ($settings['assessment_title'] ?: __('Answer the next civic scenarios', 'american-civic-bestiary'))); ?></h2>
        <?php if (!empty($settings['assessment_intro'])) : ?>
            <p class="acb-lead"><?php echo esc_html($settings['assessment_intro']); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($settings['show_name_field']) || !empty($settings['show_email_field'])) : ?>
        <div class="acb-identity">
            <?php if (!empty($settings['show_name_field'])) : ?>
                <label>
                    <span><?php esc_html_e('Name', 'american-civic-bestiary'); ?></span>
                    <input type="text" name="acb_name" value="<?php echo esc_attr($profile['respondent_name'] ?? ''); ?>" autocomplete="name">
                </label>
            <?php endif; ?>
            <?php if (!empty($settings['show_email_field'])) : ?>
                <label>
                    <span><?php esc_html_e('Email', 'american-civic-bestiary'); ?></span>
                    <input type="email" name="acb_email" value="<?php echo esc_attr($profile['respondent_email'] ?? ''); ?>" autocomplete="email">
                </label>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="acb-question-list">
        <?php echo $question_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>

    <?php if (!empty($settings['consent_text'])) : ?>
        <label class="acb-consent">
            <input type="checkbox" name="acb_consent" value="1" required>
            <span><?php echo esc_html(wp_strip_all_tags($settings['consent_text'])); ?></span>
        </label>
    <?php endif; ?>

    <input type="hidden" name="acb_action" value="submit_questions">
    <?php foreach ($questions as $question) : ?>
        <input type="hidden" name="acb_question_ids[]" value="<?php echo esc_attr((int) $question['id']); ?>">
    <?php endforeach; ?>
    <?php wp_nonce_field('acb_submit_questions', '_acb_nonce'); ?>

    <div class="acb-form__actions">
        <button class="acb-button" type="submit"><?php echo esc_html('refine' === ($flow_stage ?? '') ? __('Save refinement answers', 'american-civic-bestiary') : __('Unlock my profile', 'american-civic-bestiary')); ?></button>
    </div>
</form>
