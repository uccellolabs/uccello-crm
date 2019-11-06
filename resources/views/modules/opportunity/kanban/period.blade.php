<optgroup label="{{ uctrans('kanban.period.week', $module) }}">
    <option value="last_week">{{ uctrans('kanban.period.last_week', $module) }}</option>
    <option value="week">{{ uctrans('kanban.period.current_week', $module) }}</option>
    <option value="next_week">{{ uctrans('kanban.period.next_week', $module) }}</option>
</optgroup>

<optgroup label="{{ uctrans('kanban.period.month', $module) }}">
    <option value="last_month">{{ uctrans('kanban.period.last_month', $module) }}</option>
    <option value="month" selected>{{ uctrans('kanban.period.current_month', $module) }}</option>
    <option value="next_month">{{ uctrans('kanban.period.next_month', $module) }}</option>
</optgroup>

<optgroup label="{{ uctrans('kanban.period.quarter', $module) }}">
    <option value="last_quarter">{{ uctrans('kanban.period.last_quarter', $module) }}</option>
    <option value="quarter">{{ uctrans('kanban.period.current_quarter', $module) }}</option>
    <option value="next_quarter">{{ uctrans('kanban.period.next_quarter', $module) }}</option>
</optgroup>

<optgroup label="{{ uctrans('kanban.period.semester', $module) }}">
    <option value="last_semester">{{ uctrans('kanban.period.last_semester', $module) }}</option>
    <option value="semester">{{ uctrans('kanban.period.current_semester', $module) }}</option>
    <option value="next_semester">{{ uctrans('kanban.period.next_semester', $module) }}</option>
</optgroup>

<optgroup label="{{ uctrans('kanban.period.year', $module) }}">
    <option value="last_year">{{ uctrans('kanban.period.last_year', $module) }}</option>
    <option value="year">{{ uctrans('kanban.period.current_year', $module) }}</option>
    <option value="next_year">{{ uctrans('kanban.period.next_year', $module) }}</option>
</optgroup>