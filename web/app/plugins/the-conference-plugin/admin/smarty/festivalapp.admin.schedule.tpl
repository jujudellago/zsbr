<h2>{$Festival->getPrettyDay($Day)}</h2>
<div class="schedule-header-row">
	<div class="schedule-time-cell">&nbsp;</div>
	{foreach from=$Schedule->getParameter('ScheduleStages') item='Stage'}
	<div class="schedule-stage-cell schedule-show-cell">
		{$Stage.Name}
	</div>
	{/foreach}
	<div class="schedule-time-cell">&nbsp;</div>
</div>
<div class="schedule-time-column">
	<ul>
	{foreach from=$Times id='CanonicalTime' item='PrettyTime'}
	<li class="schedule-time-cell schedule-time-{$CanonicalTime}">{$PrettyTime}</li>
	{/foreach}
	</ul>
</div>