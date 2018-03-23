<p>The following {'Show'|pluralize} on the "{$Schedule->getParameter('ScheduleName')}" schedule have not been assigned to a day.  These {'Show'|pluralize} are therefore orphaned and will not display in the schedule for your {'Festival'|vocabulary}.</p>
{$SchedulePainter->paintSchedule($ShowListingsArray)}
