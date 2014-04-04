<div class="form-control-static">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th><strong>Account ID</strong></th>
				<th><strong>Website email</strong></th>
				<th><strong>Devise</strong></th>
				<th><strong>Solde</strong></th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$sub_accounts item='sub_account'}

				<tr>
					<td>{$sub_account->userAccountId}</td>
					<td>{$sub_account->websites->item->websiteEmail}</td>
					<td>{$sub_account->currency_label} ({$sub_account->currency})</td>
					<td>{$sub_account->balance|number_format:2:',':''} {$sub_account->currency}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
