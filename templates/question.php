<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="acb-panel acb-question" data-question-id="<?php echo esc_attr((int) $question['id']); ?>">
    <header class="acb-question__head">
        <span class="acb-question__number"><?php echo esc_html($number); ?></span>
        <div>
            <h3><?php echo wp_kses_post($question['prompt']); ?></h3>
            <?php if (!empty($question['context'])) : ?>
                <p class="acb-question__context"><?php echo wp_kses_post($question['context']); ?></p>
            <?php endif; ?>
        </div>
    </header>
    <div class="acb-options acb-options--<?php echo esc_attr($question['question_type']); ?>">
        <?php echo $options_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
</section>
