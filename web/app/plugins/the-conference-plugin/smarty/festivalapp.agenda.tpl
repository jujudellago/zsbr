{foreach from=$Shows key=ShowID item=Show}
	{if $Show->getParameter('ShowPrettyDay') ne $current_header}
		{if $current_header ne ''}
		</div> <!-- .agenda-wrapper -->
		{/if}
		<h2 class="agenda-day">{$Show->getParameter('ShowPrettyDay')}</h2>
		{assign var=current_header value=$Show->getParameter('ShowPrettyDay')}
		<div class="agenda-wrapper">
			<div class="agenda-row agenda-header-row">
				{if $parms.show_times ne 'false'}<div class="agenda-cell agenda-time-column">Time</div>{/if}
				<div class="agenda-cell agenda-description-column">Description</div>
			</div>
	{/if}
		<div class="agenda-row">
			{if $parms.show_times ne 'false'}
			<div class="agenda-cell agenda-time-column">
				{$Show->getParameter('ShowPrettyStartTime')} - {$Show->getParameter('ShowPrettyEndTime')}
			</div>
			{/if}
			<div class="agenda-cell agenda-description-column{'user__setShowClass'|apply_filters:'':$Show}">
				<div class="session-title">{$Show->getParameter('ShowTitle')}</div>
				{if $Show->getParameter('ShowPrettyStage') ne ''}<div class="session-venue"><label>{'Stage'|vocabulary}:</label> {$Show->getParameter('ShowPrettyStage')}</div>{/if}
				<div class="session-description">{$Show->getParameter('ShowDescription')}</div>
	            {if $Show->getParameter('ShowTitle') ne $Show->getArtistNames() and $Show->getArtistNames() ne ""}
	                <div class="session-speakers"><label>{'Artist'|pluralize}:</label> {$Show->getArtistNames($ArtistURL)}</div>
	
	            {/if}
			</div>
		</div>
{/foreach}
{if $current_header ne ''}
</div> <!-- .agenda-wrapper -->
{/if}
