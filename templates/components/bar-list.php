<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="acb-bars<?php echo !empty($compact) ? ' acb-bars--compact' : ''; ?>">
    <?php foreach ((array) $rows as $row) : ?>
        <?php
        $metric = max(0, min(100, (float) ($row[$metric_key] ?? 0)));
        $label = is_callable($label_callback ?? null) ? call_user_func($label_callback, $row) : ($row['label'] ?? '');
        $icon_url = !empty($show_icons) ? ACB_Assets::animal_icon_url($row['key'] ?? '') : '';
        ?>
        <div class="acb-bar">
            <div class="acb-bar__meta">
                <span>
                    <?php if ($icon_url) : ?><img class="acb-bar__icon" src="<?php echo esc_url($icon_url); ?>" alt=""><?php endif; ?>
                    <?php echo esc_html($label); ?>
                </span>
                <strong><?php echo esc_html(round($metric)); ?></strong>
            </div>
            <div class="acb-bar__track"><span style="width:<?php echo esc_attr($metric); ?>%"></span></div>
        </div>
    <?php endforeach; ?>
</div>
