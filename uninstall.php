<?php
// 防止直接访问
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 删除插件相关的所有选项
$options = [
    'rbtc_enable_plugin',
    'rbtc_enable_bold_color',
    'rbtc_enable_heading_gradient',
    'rbtc_enable_paragraph_color',
    'rbtc_enable_dark_mode_style',
    'rbtc_custom_gradient_colors',
    'rbtc_max_colored_paragraphs',

    // PRO 相关设置
    'rbtc_pro_enable_bold_optimized_color',
    'rbtc_pro_heading_gradient_template',
    'rbtc_pro_enable_3d_rotate',
    'rbtc_pro_enable_fluid_text',
    'rbtc_pro_enable_particles_mouse',
    'rbtc_pro_enable_gsap_anime',

    // 你如果还有其他设置，也放这里
];

foreach ($options as $option_name) {
    delete_option($option_name);
}
