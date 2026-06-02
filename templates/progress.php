<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="acb-panel acb-progress-panel">
    <p class="acb-eyebrow"><?php esc_html_e('Profile unlock progress', 'american-civic-bestiary'); ?></p>
    <h2><?php echo esc_html(sprintf(__('%1$d of %2$d answers complete', 'american-civic-bestiary'), (int) $answered, (int) $minimum)); ?></h2>
    <p class="acb-lead">
        <?php
        echo esc_html(sprintf(
            _n('%d more answer unlocks your civic animal profile.', '%d more answers unlock your civic animal profile.', (int) $remaining, 'american-civic-bestiary'),
            (int) $remaining
        ));
        ?>
    </p>
    <div class="acb-progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr((int) $minimum); ?>" aria-valuenow="<?php echo esc_attr((int) $answered); ?>">
        <span style="width:<?php echo esc_attr(min(100, max(0, ((int) $answered / max(1, (int) $minimum)) * 100))); ?>%"></span>
    </div>
</div>
