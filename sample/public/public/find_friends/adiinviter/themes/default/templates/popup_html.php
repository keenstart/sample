<?php


// ADI_OUTER_SHADOW_CSS_CLASS : adi_popup_outer_effect
// ADI_POPUP_ID : 

$popup_with_back_panel = '<div class="adiinviter adi_stickBorders" id="[ADI_POPUP_ID]" style="position:fixed;display: none;"><table style="width:100%;height:100%;" cellspacing="0" cellspacing="0" class="adi_clear_table adi_ppwrap"><tr class="adi_clear_tr"><td class="adi_clear_td adi_ppwrap_td"><center><table class="adi_clear_table"><tr class="adi_clear_tr"><td valign="middle" class="adi_clear_td"><div class="adi_nc_container adi_nc_popup_style alt1 adi_popup_outer_effect"><div class="adi_ppdef"><div>'.$adiinviter->phrases['adi_default_message_for_all_popups'].'</div><img src="'.adi_common_url($adiinviter->theme_url).'/images/loading.gif" width="220" height="19"></div></div></td></tr></table></center></td></tr></table></div>';

$popup_without_back_panel = '<div class="adiinviter" id="[ADI_POPUP_ID]" style="position:fixed;display: none;"><center><table cellspacing="0" cellspacing="0" class="adi_clear_table"><tr class="adi_clear_tr"><td class="adi_clear_td adi_ppwrap_td"><center><div class="adi_nc_container adi_nc_popup_style alt1"></div></center></td></tr></table></center></div>';

?>