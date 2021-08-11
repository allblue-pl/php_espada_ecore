<?php namespace EC\CookiesPolicy;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC; ?>

<div id="ECookiesPolicy_Modal" class="modal fade" data-bs-backdrop="static" 
        data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-cookies">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $_Title; ?></h5>
                <button id="eCookiesPolicy_Close" type="button" class="btn-close" 
                        aria-label="<?php echo EC\HText::_('CookiesPolicy:Close'); ?>"></button>
            </div>
            <div class="modal-body">
                <?php echo $_Body; ?>
            </div>
            <div class="modal-footer">
                <button id="eCookiesPolicy_Agree" type="button" 
                        class="btn btn-secondary">
                    <?php echo EC\HText::_('CookiesPolicy:Agree'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jsLibs.require('e-cookies-policy').init();
</script>