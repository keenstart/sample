{adi:if ($adi_current_model == 'inpage')}
{adi:if ($show_friend_adder)}

	<!-- Display startup errors -->
	{adi:template inpage_error_display}

	<!-- Go to Inviter page again -->
	<form action="" method="POST" class="adi_back_to_inviter" style="display:none;">
	<input type="hidden" name="adi_no_captcha_display" value="1">
	{adi:foreach $adiinviter->form_hidden_elements, $elem_name, $elem_val}
		<input type="hidden" name="{adi:var $elem_name}" value="{adi:var $elem_val}">
	{/adi:foreach}
	</form>

	<!-- Inpage Container for Friend Adder Interface -->
	<table class="adi_clear_table adiinviter" cellpadding="0" cellspacing="0" border="0">
	<tr class="adi_clear_tr"><td class="adi_clear_td">
		<div class="adi_nc_inpage_panel_outer" id="adi_friend_adder_root">

			<!-- Fetch common template for Friend Adder interface -->
			{adi:template friend_adder_html}

		</div>
	</td></tr></table>

	<script type="text/javascript">
	adi.allocate_intr('faip', adjq('#adi_friend_adder_root'));
	</script>

	{adi:set $load_default_template = false;}

{adi:elseif ($show_invites_sender)}

	<!-- Display startup errors -->
	{adi:template inpage_error_display}

	<!-- Go to inviter page form -->
	<form action="" method="POST" class="adi_back_to_inviter" style="display:none;">
		<input type="hidden" name="adi_no_captcha_display" value="1">
		{adi:foreach $adiinviter->form_hidden_elements, $elem_name, $elem_val}
			<input type="hidden" name="{adi:var $elem_name}" value="{adi:var $elem_val}">
		{/adi:foreach}
	</form>

	<!-- Inpage Container for Invite Sender -->
	<table class="adi_clear_table adiinviter" cellpadding="0" cellspacing="0" border="0">
		<tr class="adi_clear_tr">
			<td class="adi_clear_td">
				<div class="adi_nc_inpage_panel_outer" style="position:relative;" id="adi_invite_sender_root">
					{adi:template invite_sender_html}
				</div>
			</td>
		</tr>
	</table>


	<iframe id="adi_invite_preview_iframe" class="adi_clear_iframe" src="" style="min-height: 300px;min-width: 500px;display:none;"></iframe>

	<!-- Init Inpage Invite Sender Interface -->
	<script type="text/javascript">
	adi.allocate_intr('isip', adjq('#adi_invite_sender_root'),{numinv:{adi:var $num_invtes_js_val},totalcount:{adi:var $total_count},config:{adi:var $config_json}});
	</script>

	{adi:set $load_default_template = false;}

{/adi:if}
{/adi:if}