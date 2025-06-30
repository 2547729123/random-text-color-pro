<?php
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function() {
    if (!is_single() || (function_exists('is_amp_endpoint') && is_amp_endpoint())) return;
    if (!get_option('rbtc_enable_plugin')) return;

    // 获取开关
    $enable_pro_bold               = get_option('rbtc_pro_enable_bold_optimized_color');
    $enable_3d_rotate              = get_option('rbtc_pro_enable_3d_rotate');
    $enable_fluid_text             = get_option('rbtc_pro_enable_fluid_text');
    $enable_particles              = get_option('rbtc_pro_enable_particles_mouse');
    $enable_gsap_anime             = get_option('rbtc_pro_enable_gsap_anime');
    $enable_scroll                 = get_option('rbtc_pro_enable_scroll_effects');
    $enable_breath                 = get_option('rbtc_pro_enable_breath_animation');
    $custom_selectors              = trim(get_option('rbtc_pro_custom_selectors'));

    // 段落动态渐变色
    $enable_pro_para_dynamic_color = get_option('rbtc_pro_enable_paragraph_dynamic_color');
    $pro_para_gradient_template    = get_option('rbtc_pro_paragraph_gradient_template', 'classic_rainbow');
    $pro_para_color_change_speed   = max(1, intval(get_option('rbtc_pro_paragraph_color_change_speed', 10)));

    $pro_template = get_option('rbtc_pro_heading_gradient_template', 'classic_rainbow');

    $pro_gradients = [
        'classic_rainbow' => ['#FF0000','#FF7F00','#FFFF00','#00FF00','#0000FF','#4B0082','#8F00FF'],
        'sunset_orange'   => ['#FF4500','#FF8C00','#FFA500','#FFD700','#FFB347'],
        'ocean_blue'      => ['#0077BE','#00BFFF','#1E90FF','#00CED1','#20B2AA'],
        'plasma_flux'     => ['#12c2e9','#c471ed','#f64f59'],
    ];
    $heading_colors_js = json_encode($pro_gradients[$pro_template] ?? $pro_gradients['classic_rainbow']);
    $para_colors_js    = json_encode($pro_gradients[$pro_para_gradient_template] ?? $pro_gradients['classic_rainbow']);

    // 外部库
    if ($enable_particles) {
        wp_enqueue_script('three', 'https://cdn.jsdelivr.net/npm/three@0.158.0/build/three.min.js', [], null, true);
    }
    if ($enable_gsap_anime) {
        wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', [], null, true);
        wp_enqueue_script('anime', 'https://cdn.jsdelivr.net/npm/animejs@3.2.2/lib/anime.min.js', [], null, true);
    }
    wp_enqueue_style('tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
?>

<style>
/* 🌈 渐变流动动画 */
@keyframes gradientFlow { 0%{background-position:0%} 50%{background-position:100%} 100%{background-position:0%} }
.rbtc-pro-gradient {
    background-size: 300% 300%;
    animation: gradientFlow 6s ease infinite;
    background-clip: text; -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold; position: relative; display: inline-block;
}
/* ✨ 发光描边 + 动态光圈 */
.rbtc-pro-gradient::before {
    content: attr(data-text);
    position: absolute; top: 0; left: 0; z-index: -1;
    filter: blur(2px); color: white; opacity: 0.6;
}
.rbtc-pro-gradient::after {
    content: '';
    position: absolute; left: 50%; top: 50%; width: 120%; height: 120%;
    border-radius: 50%; border: 2px solid rgba(255,255,255,0.5);
    transform: translate(-50%, -50%);
    animation: pulseRing 2s infinite;
}
@keyframes pulseRing {
    0% {transform: scale(0.8); opacity: 0.7;}
    50% {transform: scale(1); opacity: 0.3;}
    100% {transform: scale(0.8); opacity: 0.7;}
}
/* ✅ 旋转3D */
.rbtc-rotate3d { animation: rotate3D 5s linear infinite; transform-style: preserve-3d; }
@keyframes rotate3D { from{transform: rotateX(0) rotateY(0);} to{transform: rotateX(360deg) rotateY(360deg);} }
/* ✨ 流体效果 */
.rbtc-fluid { filter: brightness(1.2) contrast(1.2) drop-shadow(0 0 5px rgba(255,255,255,0.3)); }
/* 粒子容器 */
.rbtc-particles { position: absolute; width: 100%; height: 100%; left: 0; top: 0; overflow: hidden; pointer-events: none; }
.rbtc-particles span {
    position: absolute; width: 2px; height: 2px; background: white; border-radius: 50%; opacity: 0.8;
    animation: moveParticle 3s linear infinite;
}
@keyframes moveParticle { from {transform: translateY(0);} to {transform: translateY(10px); opacity: 0;} }
/* 段落动态渐变色 */
.rbtc-pro-paragraph-gradient {
    background-clip: text; -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
    transition: background-image 1s linear;
}
/* 呼吸动画 */
<?php if ($enable_breath): ?>
.rbtc-breath {
    animation: rbtc-breath 3s ease-in-out infinite;
}
@keyframes rbtc-breath {
    0%,100% { transform: scale(1); opacity:1; }
    50%     { transform: scale(1.05); opacity:0.8; }
}
<?php endif; ?>
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const headingGradientColors = <?php echo $heading_colors_js; ?>;

    // 小标题渐变
    const headings = document.querySelectorAll('.entry-content h2, .post-content h2, .article-content h2, article h2, \
                                               .entry-content h3, .post-content h3, .article-content h3, article h3, \
                                               .entry-content h4, .post-content h4, .article-content h4, article h4, \
                                               .entry-content h5, .post-content h5, .article-content h5, article h5, \
                                               .entry-content h6, .post-content h6, .article-content h6, article h6');
    headings.forEach((el,i) => {
        const c1 = headingGradientColors[i % headingGradientColors.length];
        const c2 = headingGradientColors[(i+1) % headingGradientColors.length];
        el.style.backgroundImage = `linear-gradient(270deg, ${c1}, ${c2})`;
        el.classList.add('rbtc-pro-gradient');
        el.setAttribute('data-text', el.textContent);

        <?php if ($enable_3d_rotate): ?> el.classList.add('rbtc-rotate3d'); <?php endif; ?>
        <?php if ($enable_fluid_text): ?> el.classList.add('rbtc-fluid'); <?php endif; ?>
        <?php if ($enable_particles): ?>
        const box = document.createElement('div');
        box.className = 'rbtc-particles';
        for(let p=0;p<8;p++){
            const star = document.createElement('span');
            star.style.top = (Math.random()*100)+'%';
            star.style.left= (Math.random()*100)+'%';
            star.style.animationDuration = (2+Math.random()*2)+'s';
            box.appendChild(star);
        }
        el.style.position='relative';
        el.appendChild(box);
        <?php endif; ?>
    });

    // 加粗随机色
    <?php if ($enable_pro_bold): ?>
    const bolds = document.querySelectorAll('.entry-content strong, .post-content strong, .article-content strong, article strong, \
                                            .entry-content b, .post-content b, .article-content b, article b');
    bolds.forEach(el => {
        function getColor(){
            let r,g,b,bright;
            do{
                r=Math.floor(Math.random()*256); g=Math.floor(Math.random()*256); b=Math.floor(Math.random()*256);
                bright=r+g+b;
            } while(bright>700||bright<100);
            return `rgb(${r},${g},${b})`;
        }
        el.style.color=getColor();
    });
    <?php endif; ?>

    // 段落动态渐变色
    <?php if ($enable_pro_para_dynamic_color): ?>
    const paras = document.querySelectorAll('.entry-content p, .post-content p, .article-content p, article p');
    const paraColors = <?php echo $para_colors_js; ?>;
    const speed = <?php echo $pro_para_color_change_speed*1000; ?>;
    if(paras.length){
        let offset=0;
        function update(){
            paras.forEach((p,i)=>{
                const c1=paraColors[(i+offset)%paraColors.length];
                const c2=paraColors[(i+offset+1)%paraColors.length];
                p.style.backgroundImage=`linear-gradient(90deg,${c1},${c2})`;
                p.classList.add('rbtc-pro-paragraph-gradient');
            });
            offset=(offset+1)%paraColors.length;
        }
        update();
        setInterval(update,speed);
    }
    <?php endif; ?>

    // GSAP / Anime 动画
    <?php if ($enable_gsap_anime): ?>
    if(typeof gsap!=='undefined'){
        gsap.to('.rbtc-pro-gradient',{ duration:2, rotateY:360, repeat:-1, yoyo:true, ease:'power1.inOut' });
    }
    if(typeof anime!=='undefined'){
        anime({ targets:'.rbtc-pro-gradient', translateX:[0,5,-5,0], duration:4000, loop:true, easing:'easeInOutSine' });
    }
    <?php endif; ?>

    // 呼吸动画
    <?php if ($enable_breath): ?>
    headings.forEach(el=>el.classList.add('rbtc-breath'));
    <?php if ($enable_pro_bold): ?> if(typeof bolds!=='undefined') bolds.forEach(el=>el.classList.add('rbtc-breath')); <?php endif; ?>
    <?php if ($enable_pro_para_dynamic_color): ?> if(typeof paras!=='undefined') paras.forEach(el=>el.classList.add('rbtc-breath')); <?php endif; ?>
    <?php endif; ?>

    // 滚动视差
    <?php if ($enable_scroll): ?>
    const scrollEls=document.querySelectorAll('.entry-content h2, .entry-content h3, .entry-content p');
    window.addEventListener('scroll',()=>{
        let y=window.scrollY;
        scrollEls.forEach(el=>{ el.style.transform=`translateY(${y*0.05}px)`; });
    });
    <?php endif; ?>

    // 自定义选择器
    <?php if ($custom_selectors): ?>
    const customElements=document.querySelectorAll('<?php echo esc_js($custom_selectors); ?>');
    if(customElements.length){
        const customColors=<?php echo $heading_colors_js; ?>;
        customElements.forEach((el,i)=>{
            const c1=customColors[i%customColors.length];
            const c2=customColors[(i+1)%customColors.length];
            el.style.backgroundImage=`linear-gradient(90deg,${c1},${c2})`;
            el.style.webkitBackgroundClip='text';
            el.style.webkitTextFillColor='transparent';
            el.style.fontWeight='bold';
            el.classList.add('rbtc-pro-gradient');
        });
    }
    <?php endif; ?>
});
</script>
<?php
});
?>