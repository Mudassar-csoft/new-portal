@php
    $actionId = $actionId ?? ('student-action-' . $admission->id);
    $registrationId = $admission->registration_id ?? optional($admission->registration)->id;
    $canAdminEdit = auth()->user()?->isAdmin();
    $canUpdateStudent = auth()->user()?->hasAnyPermission(['student.update']) ?? false;
    $canDropStudent = auth()->user()?->hasAnyPermission(['student.drop']) ?? false;
    $isDroppedStudent = (string) ($admission->student_status ?? '') === 'dropped';
    $statusActions = [
        [
            'key' => 'frozen',
            'label' => 'Freeze Course',
            'icon' => 'bi bi-pause-fill text-danger',
            'icon_class' => 'lead-icon-red',
        ],
        [
            'key' => 'concluded',
            'label' => 'Conclude',
            'icon' => 'bi bi-check2-square text-success',
            'icon_class' => 'lead-icon-green',
        ],
        [
            'key' => 'dropped',
            'label' => 'Drop Student',
            'icon' => 'bi bi-person-dash-fill',
            'icon_class' => 'lead-icon-black',
        ],
        [
            'key' => 'incomplete',
            'label' => 'Not Completed',
            'icon' => 'bi bi-x-square text-danger',
            'icon_class' => 'lead-icon-red',
        ],
        [
            'key' => 'admission_cancelled',
            'label' => 'Cancel Admission/Refund',
            'icon' => 'bi bi-pause-fill text-danger',
            'icon_class' => 'lead-icon-red',
        ],
        [
            'key' => 'suspended',
            'label' => 'Suspend Student',
            'icon' => 'bi bi-exclamation-triangle text-danger',
            'icon_class' => 'lead-icon-red',
        ],
    ];
    $statusActions = $isDroppedStudent ? [] : array_values(array_filter($statusActions, static function (array $item) use ($canUpdateStudent, $canDropStudent): bool {
        return $item['key'] === 'dropped' ? $canDropStudent : $canUpdateStudent;
    }));
    $hasActionItems = $isDroppedStudent
        ? $canUpdateStudent
        : (!empty($statusActions) || $canUpdateStudent || $canAdminEdit);
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-student-partials-action-1: 24px;
            --space-student-partials-action-1: 0 !important;
            --color-student-partials-action-1: #303740;
            --color-student-partials-action-2: #efefef;
            --typo-student-partials-action-font-size-1: 16px;
            --typo-student-partials-action-font-weight-2: 500;
        }

            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 300px;
                padding: 8px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                width: 100%;
                padding: 8px 14px !important;
                color: var(--color-student-partials-action-1) !important;
                font-size: var(--typo-student-partials-action-font-size-1) !important;
                font-weight: var(--typo-student-partials-action-font-weight-2);
                line-height: 1.35;
                background: transparent !important;
                border: 0;
                text-align: left !important;
                transition: background-color 0.18s ease, color 0.18s ease;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown .dropdown-item.lead-action-item:focus,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:focus {
                background: var(--color-student-partials-action-2) !important;
                color: #222b33 !important;
                text-decoration: none;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item[disabled],
            .follow-action-dropdown form button.dropdown-item.lead-action-item[disabled] {
                opacity: 0.55;
                cursor: not-allowed;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item.is-current,
            .follow-action-dropdown form button.dropdown-item.lead-action-item.is-current {
                background: var(--color-student-partials-action-2) !important;
            }

            .follow-action-dropdown .lead-action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: var(--dimension-student-partials-action-1);
                min-width: var(--dimension-student-partials-action-1);
                height: var(--dimension-student-partials-action-1);
                font-size: var(--typo-student-partials-action-font-size-1) !important;
                line-height: 1;
                margin-right: var(--space-student-partials-action-1);
                padding: var(--space-student-partials-action-1);
            }
            .span{
                font-size: var(--typo-student-partials-action-font-size-1) !important;
            }
            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: 1.125rem !important;
                font-weight: var(--typo-student-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: var(--color-student-partials-action-1); }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #22c55e; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ff4d5a; }

            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown student-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" aria-haspopup="true" aria-expanded="false" @disabled(!$hasActionItems)>
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if($isDroppedStudent)
            @if($canUpdateStudent)
                <button
                    type="button"
                    class="dropdown-item lead-action-item js-student-reenroll"
                    data-reenroll-meta-url="{{ route('student.records.reenroll.meta', $admission) }}"
                    data-reenroll-store-url="{{ route('student.records.reenroll.store', $admission) }}"
                >
                    <span class="lead-action-icon lead-icon-green" aria-hidden="true">
                        <i class="fa fa-repeat"></i>
                    </span>
                    <span class="lead-action-label">Enroll Now</span>
                </button>
            @else
                <span class="dropdown-item lead-action-item text-muted" aria-disabled="true">
                    <span class="lead-action-label">No actions available</span>
                </span>
            @endif
        @else
            @forelse($statusActions as $item)
                @if($item['key'] === 'dropped')
                    <form method="POST" action="{{ route('student.records.drop', $admission) }}" class="js-student-drop-form" data-student-name="{{ $admission->student_name ?: 'Student' }}">
                        @csrf
                        <input type="hidden" name="drop_reason" value="">
                        <button type="submit" class="dropdown-item lead-action-item {{ $admission->student_status === $item['key'] ? 'is-current' : '' }}" @disabled($admission->student_status === $item['key'])>
                            <span class="lead-action-icon {{ $item['icon_class'] }}" aria-hidden="true">
                                <i class="fa {{ $item['icon'] }}"></i>
                            </span>
                            <span class="lead-action-label" style = "font-size: var(--typo-student-partials-action-font-size-1) !important;">{{ $item['label'] }}</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('student.records.status', $admission) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $item['key'] }}">
                        <button type="submit" class="dropdown-item lead-action-item {{ $admission->student_status === $item['key'] ? 'is-current' : '' }}" @disabled($admission->student_status === $item['key'])>
                            <span class="lead-action-icon {{ $item['icon_class'] }}" aria-hidden="true">
                                <i class="fa {{ $item['icon'] }}"></i>
                            </span>
                            <span class="lead-action-label" style = "font-size: var(--typo-student-partials-action-font-size-1) !important;">{{ $item['label'] }}</span>
                        </button>
                    </form>
                @endif
            @empty
                @if(!$canUpdateStudent && !$canAdminEdit)
                    <span class="dropdown-item lead-action-item text-muted" aria-disabled="true">
                        <span class="lead-action-label">No actions available</span>
                    </span>
                @endif
            @endforelse

            <button
                type="button"
                class="dropdown-item lead-action-item js-student-transfer"
                data-transfer-meta-url="{{ route('student.records.transfer.meta', $admission) }}"
                data-transfer-store-url="{{ route('student.records.transfer.store', $admission) }}"
                @disabled(!$canUpdateStudent)
            >
                <span class="lead-action-icon lead-icon-blue text-info" aria-hidden="true">
                    <i class="fa fa-map-o"></i>
                </span>
                <span class="lead-action-label">Transfer Campus &amp; Batch</span>
            </button>

            @if($canAdminEdit)
                <a class="dropdown-item lead-action-item {{ $registrationId ? '' : 'disabled' }}" href="{{ $registrationId ? route('student.show', $registrationId) : '#' }}" @if(!$registrationId) aria-disabled="true" tabindex="-1" @endif>
                    <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                        <i class="fa fa-pencil-square-o"></i>
                    </span>
                    <span class="lead-action-label">Edit</span>
                </a>
            @endif
        @endif
    </div>
</div>
