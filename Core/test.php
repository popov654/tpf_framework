<?php ob_start(); ?>
    <p class="centered">
        This is a template test. The value of <strong>x</strong> is <strong>{{ x }}</strong>.
        The value of <strong>user.accounts[0]</strong> is <strong>{{ user.accounts[0] }}</strong>.
    </p>
<?php $content = ob_get_clean();