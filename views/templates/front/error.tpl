<div>
	<h3>{l s='An error occurred' mod='psphipay'}:</h3>
	<ul class="alert alert-danger">
		{foreach from=$errors item='error'}
			<li>{$error}.</li>
		{/foreach}
	</ul>
</div>