<?php
/*
 * Plugin Name: Random Text Color – 随机彩色文字 PRO版
 * Plugin URI: https://github.com/2547729123/random-text-color-pro
 * Description: 为加粗文字、段落、小标题添加彩色样式，支持深色模式、自定义渐变配置，所有模块可单独开关控制。
 * Version: 1.0
 * Author: 码铃薯
 * Author URI: https://www.tudoucode.cn
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: random-text-color-pro
 * Domain Path: /languages/
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.0
 */

if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . 'includes/file-integrity-check.php';
require_once plugin_dir_path(__FILE__) . 'includes/license-handler.php';
$plugin_id = basename(dirname(__FILE__)); 
/**
 * 注册设置项（免费 + PRO）
 */
add_action('admin_init', function() {
    register_setting('rbtc_settings_group', 'rbtc_enable_plugin', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_enable_bold_color', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_enable_heading_gradient', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_enable_paragraph_color', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_enable_dark_mode_style', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_custom_gradient_colors', [
        'sanitize_callback' => function($input) {
            $colors = explode(',', $input);
            $valid = [];
            foreach($colors as $c) {
                $c = trim($c);
                if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $c)) {
                    $valid[] = strtoupper($c);
                }
            }
            return count($valid) ? implode(',', array_slice($valid,0,10)) : '#FF0000,#FF9900,#33CC33';
        }
    ]);
    register_setting('rbtc_settings_group', 'rbtc_max_colored_paragraphs', [
        'sanitize_callback' => function($input) {
            $num = intval($input);
            return max(1, min(20, $num));
        }
    ]);

    register_setting('rbtc_settings_group', 'rbtc_pro_enable_bold_optimized_color', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_pro_heading_gradient_template', [
        'sanitize_callback' => function($input) {
            $templates = ['classic_rainbow','sunset_orange','ocean_blue','plasma_flux'];
            return in_array($input,$templates) ? $input : 'classic_rainbow';
        }
    ]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_3d_rotate', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_fluid_text', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_particles_mouse', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_gsap_anime', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
	register_setting('rbtc_settings_group', 'rbtc_pro_enable_paragraph_dynamic_color', ['sanitize_callback' => function($input) { return $input === '1' ? 1 : 0; }]);
	register_setting('rbtc_settings_group', 'rbtc_pro_paragraph_gradient_template', [
    'sanitize_callback' => function($input) {
        $templates = ['classic_rainbow','sunset_orange','ocean_blue','plasma_flux'];
        return in_array($input, $templates) ? $input : 'classic_rainbow';
	    }
	]);
	register_setting('rbtc_settings_group', 'rbtc_pro_paragraph_color_change_speed', [
    'sanitize_callback' => function($input) {
        $speed = intval($input);
        return ($speed >= 1 && $speed <= 60) ? $speed : 10;
	    }
	]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_scroll_effects', [
    'sanitize_callback' => function($input){ return $input === '1' ? 1 : 0; }
    ]);
    register_setting('rbtc_settings_group', 'rbtc_pro_enable_breath_animation', [
    'sanitize_callback' => function($input){ return $input === '1' ? 1 : 0; }
    ]);
	register_setting('rbtc_settings_group', 'rbtc_pro_custom_selectors', [
    'sanitize_callback' => function($input) {
        return sanitize_text_field($input);
    }
    ]);

});

/**
 * 加载语言包
 */
add_action('plugins_loaded', function(){
    load_plugin_textdomain('random-text-color-pro', false, dirname(plugin_basename(__FILE__)).'/languages/');
});

/**
 * 后台菜单
 */
add_action('admin_menu', function(){
    add_options_page(
        __('Random Text Color 设置','random-text-color-pro'),
        __('Random Text Color','random-text-color-pro'),
        'manage_options',
        'rbtc-settings',
        'rbtc_render_settings_page'
    );
});

/**
 * 后台设置页
 */
function rbtc_render_settings_page() {
?>
<div class="wrap">
    <h1><?php esc_html_e('随机彩色文字设置', 'random-text-color-pro'); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields('rbtc_settings_group'); ?>

        <!-- 全局总开关 -->
        <h2>插件总开关</h2>
        <table class="form-table">
            <tr>
                <th>启用插件</th>
                <td>
                    <input type="checkbox" name="rbtc_enable_plugin" value="1" <?php checked(1, get_option('rbtc_enable_plugin')); ?> />
                    <span class="description">必须开启，下面所有免费/PRO 功能才能生效。</span>
                </td>
            </tr>
        </table>

        <!-- Tab 导航 -->
        <h2 class="nav-tab-wrapper">
            <a href="#" class="nav-tab nav-tab-active" data-tab="free">免费功能</a>
            <a href="#" class="nav-tab" data-tab="pro">PRO 功能</a>
        </h2>

        <!-- 免费功能 Tab -->
        <div id="rbtc-tab-free" class="rbtc-tab-content active">
            <table class="form-table">
                <tr><th>加粗文字随机色</th><td><input type="checkbox" name="rbtc_enable_bold_color" value="1" <?php checked(1, get_option('rbtc_enable_bold_color')); ?> /></td></tr>
                <tr><th>小标题渐变</th><td><input type="checkbox" name="rbtc_enable_heading_gradient" value="1" <?php checked(1, get_option('rbtc_enable_heading_gradient')); ?> /></td></tr>
                <tr><th>段落随机色</th><td><input type="checkbox" name="rbtc_enable_paragraph_color" value="1" <?php checked(1, get_option('rbtc_enable_paragraph_color')); ?> /></td></tr>
                <tr><th>深色模式样式</th><td><input type="checkbox" name="rbtc_enable_dark_mode_style" value="1" <?php checked(1, get_option('rbtc_enable_dark_mode_style')); ?> /></td></tr>
                <tr>
                    <th>自定义渐变色（英文逗号分隔）</th>
                    <td><input type="text" name="rbtc_custom_gradient_colors" value="<?php echo esc_attr(get_option('rbtc_custom_gradient_colors')); ?>" size="60"></td>
                </tr>
                <tr>
                    <th>最多着色段落数</th>
                    <td><input type="number" name="rbtc_max_colored_paragraphs" value="<?php echo esc_attr(get_option('rbtc_max_colored_paragraphs')); ?>" min="1" max="20"></td>
                </tr>
            </table>
        </div>

<div id="rbtc-tab-pro" class="rbtc-tab-content">
    <table class="form-table">
        <tr>
            <th>加粗智能优化色</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_bold_optimized_color" value="1" <?php checked(1, get_option('rbtc_pro_enable_bold_optimized_color')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>小标题渐变模板</th>
            <td>
                <select name="rbtc_pro_heading_gradient_template" <?php echo mdl_pro_attr('', 'disabled'); ?>>
                    <?php
                    $options = [
                        'classic_rainbow' => '经典彩虹',
                        'sunset_orange' => '日落橙',
                        'ocean_blue' => '海洋蓝',
                        'plasma_flux' => '等离子流'
                    ];
                    $current = get_option('rbtc_pro_heading_gradient_template', 'classic_rainbow');
                    foreach ($options as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($current, $k, false) . '>' . esc_html($v) . '</option>';
                    }
                    ?>
                </select>
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——3D旋转特效</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_3d_rotate" value="1" <?php checked(1, get_option('rbtc_pro_enable_3d_rotate')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——流体发光文字</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_fluid_text" value="1" <?php checked(1, get_option('rbtc_pro_enable_fluid_text')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——鼠标粒子特效</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_particles_mouse" value="1" <?php checked(1, get_option('rbtc_pro_enable_particles_mouse')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——GSAP / Anime.js 动画</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_gsap_anime" value="1" <?php checked(1, get_option('rbtc_pro_enable_gsap_anime')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>段落动态渐变色</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_paragraph_dynamic_color" value="1" <?php checked(1, get_option('rbtc_pro_enable_paragraph_dynamic_color')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>段落渐变模板</th>
            <td>
                <select name="rbtc_pro_paragraph_gradient_template" <?php echo mdl_pro_attr('', 'disabled'); ?>>
                    <?php
                    $options = [
                        'classic_rainbow' => '经典彩虹',
                        'sunset_orange' => '日落橙',
                        'ocean_blue' => '海洋蓝',
                        'plasma_flux' => '等离子流'
                    ];
                    $current = get_option('rbtc_pro_paragraph_gradient_template', 'classic_rainbow');
                    foreach ($options as $k => $v) {
                        echo '<option value="' . esc_attr($k) . '"' . selected($current, $k, false) . '>' . esc_html($v) . '</option>';
                    }
                    ?>
                </select>
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>变色速度（秒）</th>
            <td>
                <input type="number" name="rbtc_pro_paragraph_color_change_speed" value="<?php echo esc_attr(get_option('rbtc_pro_paragraph_color_change_speed', 10)); ?>" min="1" max="60" <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——滚动视差动效</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_scroll_effects" value="1" <?php checked(1, get_option('rbtc_pro_enable_scroll_effects')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>——呼吸律动动画</th>
            <td>
                <input type="checkbox" name="rbtc_pro_enable_breath_animation" value="1" <?php checked(1, get_option('rbtc_pro_enable_breath_animation')); ?> <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
            </td>
        </tr>

        <tr>
            <th>自定义标签 / 选择器</th>
            <td>
                <input type="text" name="rbtc_pro_custom_selectors" value="<?php echo esc_attr(get_option('rbtc_pro_custom_selectors', '')); ?>" size="60" <?php echo mdl_pro_attr('', 'disabled'); ?> />
                <?php echo mdl_pro_attr('', 'text'); ?>
                <p class="description">例如：blockquote, .my-class, span.special</p>
            </td>
        </tr>
    </table>
</div>

        <?php submit_button(); ?>
    </form>

    <!-- Tab 切换脚本 -->
    <style>
        .rbtc-tab-content { display: none; }
        .rbtc-tab-content.active { display: block; }
        .nav-tab-wrapper { margin-bottom: 10px; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-tab').forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nav-tab').forEach(function(t) { t.classList.remove('nav-tab-active'); });
                document.querySelectorAll('.rbtc-tab-content').forEach(function(c) { c.classList.remove('active'); });
                this.classList.add('nav-tab-active');
                document.getElementById('rbtc-tab-' + this.dataset.tab).classList.add('active');
            });
        });
    });
    </script>
</div>
<?php
}
/**
 * 前端样式和脚本输出（免费+PRO功能集成版）
 */
add_action('wp_footer', function(){
    if (!is_single() || (function_exists('is_amp_endpoint') && is_amp_endpoint())) return;
    if (!get_option('rbtc_enable_plugin')) return;

    // 免费功能设置
    $enable_bold = get_option('rbtc_enable_bold_color');
    $enable_headings = get_option('rbtc_enable_heading_gradient');
    $enable_para = get_option('rbtc_enable_paragraph_color');
    $enable_dark = get_option('rbtc_enable_dark_mode_style');
    $custom_colors = get_option('rbtc_custom_gradient_colors', '#FF0000,#FF9900,#33CC33');
    $gradient_array = explode(',', $custom_colors);
    $gradient_array = array_map('trim', $gradient_array);
    $gradient_array = array_filter($gradient_array, function($color) {
        return preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color);
    });
    $gradient_array = array_slice($gradient_array, 0, 10);
    $max_para = intval(get_option('rbtc_max_colored_paragraphs', 5));
    if ($max_para < 1) $max_para = 1;
    if ($max_para > 20) $max_para = 20;

    // PRO 功能默认关闭
    $enable_pro_bold = false;
    $pro_template = 'classic_rainbow';
    $enable_3d_rotate = false;
    $enable_fluid_text = false;
    $enable_particles = false;
    $enable_gsap_anime = false;

    // PRO 小标题独家渐变模板
    $pro_gradients = [
        'classic_rainbow' => ['#FF0000','#FF7F00','#FFFF00','#00FF00','#0000FF','#4B0082','#8F00FF'],
        'sunset_orange' => ['#FF4500','#FF8C00','#FFA500','#FFD700','#FFB347'],
        'ocean_blue' => ['#0077BE','#00BFFF','#1E90FF','#00CED1','#20B2AA'],
        'plasma_flux' => ['#FF00FF','#8A2BE2','#00FFFF','#7FFF00','#FF4500'],
    ];

    $pro_gradient_colors = $pro_gradients['classic_rainbow'];
    $free_gradient_colors_js = json_encode($gradient_array);
    $pro_gradient_colors_js = json_encode($pro_gradient_colors);

    // 全局授权锁判断，未授权时禁止PRO功能
    if (function_exists('my_plugin_check_pro_license') && my_plugin_check_pro_license()) {
        // 授权通过，加载PRO配置
        $enable_pro_bold = get_option('rbtc_pro_enable_bold_optimized_color');
        $pro_template = get_option('rbtc_pro_heading_gradient_template', 'classic_rainbow');
        $enable_3d_rotate = get_option('rbtc_pro_enable_3d_rotate');
        $enable_fluid_text = get_option('rbtc_pro_enable_fluid_text');
        $enable_particles = get_option('rbtc_pro_enable_particles_mouse');
        $enable_gsap_anime = get_option('rbtc_pro_enable_gsap_anime');

        if (isset($pro_gradients[$pro_template])) {
            $pro_gradient_colors = $pro_gradients[$pro_template];
            $pro_gradient_colors_js = json_encode($pro_gradient_colors);
        }
    }

    // 输出样式部分
    echo "<style>";
    // 深色模式样式（免费功能）
    if ($enable_dark) {
        echo "@media (prefers-color-scheme: dark) {
            .entry-content, .post-content, .article-content, article { color: #e0e0e0 !important; }
            .rainbow-gradient-text { color: transparent !important; }
            p { color: #d0d0d0 !important; }
            strong, b { color: #ffcc00 !important; }
        }";
    }

    // 渐变标题基础样式
    echo ".rainbow-gradient-text {
        background-clip: text; -webkit-background-clip: text;
        -webkit-text-fill-color: transparent; text-fill-color: transparent;
        font-weight: bold; display: inline;
    }";

    // PRO 3D旋转效果样式
    if ($enable_3d_rotate) {
        echo ".rbtc-3d-rotate {
            display: inline-block;
            transform-style: preserve-3d;
            animation: rbtc-3d-rotate 10s linear infinite;
            font-weight: bold;
        }
        @keyframes rbtc-3d-rotate {
            0% { transform: rotateX(0deg) rotateY(0deg); }
            100% { transform: rotateX(360deg) rotateY(360deg); }
        }";
    }

    // PRO 流体发光描边效果
    if ($enable_fluid_text) {
        echo ".rbtc-fluid-glow {
            position: relative;
            color: #fff;
            text-shadow:
                0 0 5px #ff00de,
                0 0 10px #ff00de,
                0 0 20px #ff00de,
                0 0 40px #ff00de;
            animation: rbtc-glow-pulse 3s ease-in-out infinite;
            font-weight: bold;
        }
        @keyframes rbtc-glow-pulse {
            0%, 100% {
                text-shadow:
                    0 0 5px #ff00de,
                    0 0 10px #ff00de,
                    0 0 20px #ff00de,
                    0 0 40px #ff00de;
            }
            50% {
                text-shadow:
                    0 0 10px #ff6eff,
                    0 0 20px #ff6eff,
                    0 0 30px #ff6eff,
                    0 0 50px #ff6eff;
            }
        }";
    }
    echo "</style>";

    // PRO 粒子画布
    if ($enable_particles) {
        echo '<canvas id="rbtc-particles-canvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:999999;"></canvas>';
    }

    // 输出JS部分
    ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const content = document.querySelector('.entry-content, .post-content, .article-content, article');
    if (!content) return;

    const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    const bolds = content.querySelectorAll('strong, b');
    const headings = content.querySelectorAll('h2, h3, h4, h5, h6');
    const paras = content.querySelectorAll('p');

    const freeGradientColors = <?php echo $free_gradient_colors_js; ?>;
    const proGradientColors = <?php echo $pro_gradient_colors_js; ?>;

    function getRandomColor() {
        function rand(min=0,max=255){return Math.floor(Math.random()*(max-min+1))+min;}
        let r,g,b,brightness;
        do {
            r=rand(); g=rand(); b=rand();
            brightness = r+g+b;
            if (!isDarkMode && brightness > 600) continue;
            if (isDarkMode && brightness < 200) continue;
            break;
        } while(true);
        return `rgb(${r},${g},${b})`;
    }

    // 免费功能：加粗随机色
    <?php if ($enable_bold): ?>
    bolds.forEach(el => {
        el.style.color = getRandomColor();
    });
    <?php endif; ?>

    // PRO 功能：加粗智能优化色（覆盖免费）
    <?php if ($enable_pro_bold): ?>
    bolds.forEach(el => {
        function getOptimizedColor() {
            let r,g,b,brightness;
            do {
                r=Math.floor(Math.random()*256);
                g=Math.floor(Math.random()*256);
                b=Math.floor(Math.random()*256);
                brightness = r+g+b;
            } while (brightness > 700 || brightness < 100);
            return `rgb(${r},${g},${b})`;
        }
        el.style.color = getOptimizedColor();
    });
    <?php endif; ?>

    // 免费功能：小标题渐变色
    <?php if ($enable_headings): ?>
    headings.forEach((el,i) => {
        const c1 = freeGradientColors[i % freeGradientColors.length];
        const c2 = freeGradientColors[(i+1) % freeGradientColors.length];
        el.style.backgroundImage = `linear-gradient(to right, ${c1}, ${c2})`;
        el.style.backgroundSize = '100% 100%';
        el.classList.add('rainbow-gradient-text');
    });
    <?php endif; ?>

    // PRO 小标题独家渐变模板（覆盖免费）
    headings.forEach((el,i) => {
        const c1 = proGradientColors[i % proGradientColors.length];
        const c2 = proGradientColors[(i+1) % proGradientColors.length];
        el.style.backgroundImage = `linear-gradient(to right, ${c1}, ${c2})`;
        el.style.backgroundSize = '100% 100%';
        el.style.webkitBackgroundClip = 'text';
        el.style.webkitTextFillColor = 'transparent';
        el.style.fontWeight = 'bold';
        el.classList.add('rainbow-gradient-text');
    });

    // PRO 3D旋转特效
    <?php if ($enable_3d_rotate): ?>
    headings.forEach(el => {
        el.classList.add('rbtc-3d-rotate');
    });
    <?php endif; ?>

    // PRO 流体发光描边
    <?php if ($enable_fluid_text): ?>
    bolds.forEach(el => {
        el.classList.add('rbtc-fluid-glow');
    });
    <?php endif; ?>

    // 免费功能：段落随机色，最多染色数限制
    <?php if ($enable_para): ?>
    for (let i=0; i < Math.min(<?php echo $max_para; ?>, paras.length); i++) {
        paras[i].style.color = getRandomColor();
    }
    <?php endif; ?>

    // PRO 粒子特效：鼠标跟随粒子
    <?php if ($enable_particles): ?>
    (function(){
        const canvas = document.getElementById('rbtc-particles-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let width, height;
        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        const particles = [];
        const maxParticles = 50;

        function randomInt(min,max){return Math.floor(Math.random()*(max-min+1))+min;}
        function randomFloat(min,max){return Math.random()*(max-min)+min;}

        function Particle(x,y){
            this.x = x;
            this.y = y;
            this.radius = randomFloat(1,4);
            this.color = `hsl(${randomInt(0,360)}, 100%, 70%)`;
            this.vx = randomFloat(-1,1);
            this.vy = randomFloat(-1,1);
            this.life = 100;
        }
        Particle.prototype.update = function(){
            this.x += this.vx;
            this.y += this.vy;
            this.life--;
            if(this.life < 0) this.radius -= 0.1;
        };
        Particle.prototype.draw = function(ctx){
            ctx.beginPath();
            ctx.fillStyle = this.color;
            ctx.shadowColor = this.color;
            ctx.shadowBlur = 8;
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2);
            ctx.fill();
        };

        function animate(){
            ctx.clearRect(0,0,width,height);
            for(let i=particles.length-1; i>=0; i--){
                let p = particles[i];
                p.update();
                if(p.radius <= 0){
                    particles.splice(i,1);
                } else {
                    p.draw(ctx);
                }
            }
            requestAnimationFrame(animate);
        }

        window.addEventListener('mousemove', e => {
            if (particles.length < maxParticles) {
                particles.push(new Particle(e.clientX, e.clientY));
            }
        });

        animate();
    })();
    <?php endif; ?>

    // PRO GSAP/Anime.js 动画示范
    <?php if ($enable_gsap_anime): ?>
    if (typeof gsap !== 'undefined') {
        headings.forEach(el => {
            gsap.to(el, {duration: 2, rotation: 360, repeat: -1, ease: "linear"});
        });
    } else if (typeof anime !== 'undefined') {
        anime({
            targets: '.entry-content h2, .entry-content h3, .entry-content h4, .entry-content h5, .entry-content h6',
            rotate: '1turn',
            duration: 4000,
            loop: true,
            easing: 'linear'
        });
    }
    <?php endif; ?>
});
</script>
<?php
});

/**
 * 插件列表页设置和授权码链接
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_url = esc_url(admin_url('options-general.php?page=rbtc-settings'));
    $settings_link = '<a href="' . $settings_url . '">' . esc_html__('设置', 'random-text-color-pro') . '</a>';

    $license_url = esc_url(admin_url('options-general.php?page=mdl-plugin-license')); // 授权管理页

    // 检查授权状态
    if (my_plugin_check_pro_license('random-text-color-pro')) {
        $license_link = '<a href="' . $license_url . '" style="color:green;font-weight:bold;">' . esc_html__('✅ 已授权', 'random-text-color-pro') . '</a>';
    } else {
        $license_link = '<a href="' . $license_url . '" style="color:#d54e21;">' . esc_html__('🚀 输入授权码', 'random-text-color-pro') . '</a>';
    }

    array_unshift($links, $settings_link);
    $links[] = $license_link;

    return $links;
});


/**
 * 条件加载 PRO 功能文件
 */
add_action('init', function() {
    if (get_option('rbtc_pro_enable_bold_optimized_color') || get_option('rbtc_pro_heading_gradient_template')) {
        include_once plugin_dir_path(__FILE__) . 'pro/pro.php';
    }
});