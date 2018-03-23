{assign var="PrettyDays" value=$Festival->getPrettyDays()}
<div id="schedule-control-drawer">
<div id="schedule-control-pane">
	<ul id="schedules-list">
		{foreach from=$Schedules key='schedule_id' item='Schedule'}
		<li id="schedule-{$Schedule->getParameter('ScheduleUID')}" class="schedule-list-li"><span class="schedule-name">{$Schedule->getParameter('ScheduleName')}</span>
			<ul class="schedule-options">
				{foreach from=$Schedule->getParameter('SchedulePrettyDays') key='day_id' item='PrettyDay'}
				<li id="schedule-{$Schedule->getParameter('ScheduleUID')}-day-{$day_id}" class="festival-day day-{$day_id}">{$PrettyDay}</li>
				{/foreach}
				{if $Schedule->getParameter('ScheduleOrphanShows')|@is_array}
				<li id="schedule-{$Schedule->getParameter('ScheduleUID')}-orphans" class="schedule-settings"><a id="schedule-orphans-{$Schedule->getParameter('ScheduleUID')}" class="schedule-orphans" title="Orphaned {'Show'|pluralize}">No Day Assigned ({$Schedule->getParameter('ScheduleOrphanShows')|@count})</a></li>
				{/if}
				<li id="schedule-{$Schedule->getParameter('ScheduleUID')}-settings" class="schedule-settings"><a id="delete-schedule-{$Schedule->getParameter('ScheduleUID')}" class="delete-this-schedule" title="Delete this Schedule">delete</a> <a title="Change Settings" id="this-schedule-settings-{$Schedule->getParameter('ScheduleUID')}" class="this-schedule-settings">settings</a></li>
			</ul>
		</li>
		{/foreach}
		<li id="schedule-new" class="schedule-list-li"><span class="schedule-name">+</span></li>
		<li id="schedule-statistics" class="schedule-list-li"><span class="schedule-name">Statistics</span></li>
	</ul>
</div>
</div>
