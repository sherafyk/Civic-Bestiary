<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="acb-dashboard">
    <div class="acb-result-head acb-panel">
        <div class="acb-result-head__copy">
            <p class="acb-eyebrow"><?php echo esc_html($minimum_met ? ($settings['dashboard_eyebrow'] ?: __('Your civic animal profile', 'american-civic-bestiary')) : __('Calibration in progress', 'american-civic-bestiary')); ?></p>
            <h2><?php echo esc_html($title); ?></h2>
            <p class="acb-lead"><?php echo esc_html($minimum_met ? ($settings['dashboard_intro'] ?: ($primary['summary'] ?? '')) : $calibration_copy); ?></p>
            <div class="acb-inline-meta">
                <span><?php echo esc_html(sprintf(__('Answered: %d', 'american-civic-bestiary'), (int) ($result['meta']['total_answered'] ?? 0))); ?></span>
                <span><?php echo esc_html(sprintf(__('Confidence: %s', 'american-civic-bestiary'), ucfirst((string) ($result['meta']['confidence_label'] ?? '')))); ?></span>
                <span><?php echo esc_html(sprintf(__('Blend: %s', 'american-civic-bestiary'), $blend['label'] ?? '')); ?></span>
            </div>
            <?php if ($cta_url && $cta_label) : ?>
                <p class="acb-cta"><a class="acb-button acb-button--ghost" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a></p>
            <?php endif; ?>
        </div>
        <div class="acb-score-ring" aria-label="<?php esc_attr_e('Up-vs-Down Index', 'american-civic-bestiary'); ?>">
            <strong><?php echo esc_html(round((float) ($result['updown']['index'] ?? 0))); ?></strong>
            <span><?php esc_html_e('Up-vs-Down', 'american-civic-bestiary'); ?></span>
            <small><?php echo esc_html($result['updown']['band']['label'] ?? ''); ?></small>
        </div>
    </div>

    <?php if ($minimum_met) : ?>
        <div class="acb-grid acb-grid--three">
            <article class="acb-panel acb-profile-card">
                <?php if (!empty($settings['show_icons']) && $primary_icon) : ?>
                    <img class="acb-profile-card__icon" src="<?php echo esc_url($primary_icon); ?>" alt="<?php echo esc_attr($primary['label'] ?? ''); ?>">
                <?php endif; ?>
                <div>
                    <h3><?php echo esc_html($primary['label'] ?? ''); ?></h3>
                    <p class="acb-kicker"><?php echo esc_html($primary['title'] ?? ''); ?></p>
                    <p><?php echo esc_html($primary['gift'] ?? ''); ?></p>
                    <?php if (!empty($primary['motto'])) : ?><p class="acb-muted"><em><?php echo esc_html($primary['motto']); ?></em></p><?php endif; ?>
                </div>
            </article>
            <article class="acb-panel acb-profile-card">
                <?php if (!empty($settings['show_icons']) && $secondary_icon) : ?>
                    <img class="acb-profile-card__icon" src="<?php echo esc_url($secondary_icon); ?>" alt="<?php echo esc_attr($secondary['label'] ?? ''); ?>">
                <?php endif; ?>
                <div>
                    <h3><?php echo esc_html($secondary['label'] ?? ''); ?></h3>
                    <p class="acb-kicker"><?php echo esc_html($secondary['title'] ?? ''); ?></p>
                    <p><?php echo esc_html($secondary['gift'] ?? ''); ?></p>
                    <?php if (!empty($secondary['motto'])) : ?><p class="acb-muted"><em><?php echo esc_html($secondary['motto']); ?></em></p><?php endif; ?>
                </div>
            </article>
            <article class="acb-panel acb-profile-card">
                <div>
                    <h3><?php echo esc_html($house['label'] ?? ''); ?></h3>
                    <p class="acb-kicker"><?php echo esc_html($blend['label'] ?? ''); ?></p>
                    <p><?php echo esc_html($house['instinct'] ?? ''); ?></p>
                    <?php if (!empty($primary['danger'])) : ?><p class="acb-muted"><strong><?php esc_html_e('Watch-out:', 'american-civic-bestiary'); ?></strong> <?php echo esc_html($primary['danger']); ?></p><?php endif; ?>
                </div>
            </article>
        </div>

        <div class="acb-grid acb-grid--two">
            <article class="acb-panel">
                <h3><?php esc_html_e('Profile interpretation', 'american-civic-bestiary'); ?></h3>
                <ul class="acb-bullets">
                    <?php if (!empty($primary['core_question'])) : ?><li><strong><?php esc_html_e('Core question:', 'american-civic-bestiary'); ?></strong> <?php echo esc_html($primary['core_question']); ?></li><?php endif; ?>
                    <?php if (!empty($primary['summary'])) : ?><li><strong><?php esc_html_e('Summary:', 'american-civic-bestiary'); ?></strong> <?php echo esc_html($primary['summary']); ?></li><?php endif; ?>
                    <?php if (!empty($primary['capture'])) : ?><li><strong><?php esc_html_e('Capture risk:', 'american-civic-bestiary'); ?></strong> <?php echo esc_html($primary['capture']); ?></li><?php endif; ?>
                    <?php if (!empty($primary['corrective'])) : ?><li><strong><?php esc_html_e('Corrective:', 'american-civic-bestiary'); ?></strong> <?php echo esc_html($primary['corrective']); ?></li><?php endif; ?>
                </ul>
            </article>
            <article class="acb-panel">
                <h3><?php esc_html_e('Top animal matches', 'american-civic-bestiary'); ?></h3>
                <?php echo $animal_bars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </article>
        </div>
    <?php endif; ?>

    <div class="acb-grid acb-grid--two">
        <article class="acb-panel">
            <h3><?php esc_html_e('Twelve-dimension civic spectrum', 'american-civic-bestiary'); ?></h3>
            <?php echo $dimension_bars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </article>
        <?php if (!empty($settings['show_house_scores'])) : ?>
            <article class="acb-panel">
                <h3><?php esc_html_e('House alignment', 'american-civic-bestiary'); ?></h3>
                <?php echo $house_bars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </article>
        <?php endif; ?>
    </div>

    <?php if (!empty($settings['show_capture_overlay'])) : ?>
        <div class="acb-grid acb-grid--single">
            <article class="acb-panel">
                <h3><?php esc_html_e('Capture-literacy overlay', 'american-civic-bestiary'); ?></h3>
                <p class="acb-kicker"><?php echo esc_html($result['updown']['band']['label'] ?? ''); ?></p>
                <?php echo $capture_bars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </article>
        </div>
    <?php endif; ?>
</section>
