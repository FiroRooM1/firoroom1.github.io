<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['url']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['url']); ?>
<?php foreach (array_filter((['url']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<tr>
<td class="header">
<a href="<?php echo e($url); ?>" style="display: inline-block;">
<img src="https://via.placeholder.com/200x60/1a1a2e/ffffff?text=League+of+Legends+Recruitment" alt="League of Legends Recruitment" style="border: none; -ms-interpolation-mode: bicubic; max-width: 100%;">
</a>
</td>
</tr>
<?php /**PATH C:\Users\1\Documents\引き継ぎ\laravel\resources\views/vendor/mail/html/header.blade.php ENDPATH**/ ?>