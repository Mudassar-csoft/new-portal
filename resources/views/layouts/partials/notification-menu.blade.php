@php($quickLeads = $webLeadNotifications['quick_lead'] ?? collect())
@php($websiteEnrollments = $webLeadNotifications['website_enrollment'] ?? collect())
@php($websiteAdmissions = $webLeadNotifications['website_admission'] ?? collect())
@php($brochureDownloads = $webLeadNotifications['brochure_download'] ?? collect())
@php($overdueInvoices = $invoiceOverdueNotifications ?? collect())
@php($followupItems = $followupNotifications ?? collect())
@php($coworkingDueItems = $coworkingDueNotifications ?? collect())
@php($hasWebLeadNotifications = (bool) ($canViewWebLeadNotifications ?? false))
@php($hasFollowupNotifications = (bool) ($canViewFollowupNotifications ?? false))
@php($hasInvoiceNotifications = (bool) ($canViewInvoiceNotifications ?? false))
@php($hasCoworkingDueNotifications = (bool) ($canViewCoworkingDueNotifications ?? false))
@php($hasVisibleNotificationPanels = $hasWebLeadNotifications || $hasFollowupNotifications || $hasInvoiceNotifications || $hasCoworkingDueNotifications)
@php($notificationMoreLinks = [
    'follow_up' => route('leads.followups'),
    'quick_lead' => route('web-leads.index', ['tab' => 'quick_lead']),
    'website_enrollment' => route('web-leads.index', ['tab' => 'website_enrollment']),
    'website_admission' => route('web-leads.index', ['tab' => 'website_admission']),
    'brochure_download' => route('web-leads.index', ['tab' => 'brochure_download']),
    'overdue_invoices' => route('finance.receivables', ['status' => 'overdue']),
])

@if(! $hasVisibleNotificationPanels)
  <div class="text-center p-4 text-muted">No notifications available.</div>
@else
  <div class="notif-accordion">
    @if($hasFollowupNotifications)
      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-follow-up" aria-expanded="false">
          <span>Leads Follow Up</span>
          <span class="count">{{ $followupNotificationCount ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-follow-up">
          @if ($followupItems->isEmpty())
            <div class="text-center p-4 text-muted">No Follow Up notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($followupItems->take(3) as $followup)
                    @php($notificationDueAt = $followup->notification_due_at ?? $followup->next_action_date)
                    <tr>
                      <td>
                        @if(!empty($followup->is_placeholder))
                          <span class="notification-name-link">{{ $followup->lead->name ?? 'N/A' }}</span>
                          <div class="text-muted">{{ $followup->counselor ?? 'Counselor' }} - {{ $followup->status ?? 'Pending' }}</div>
                        @else
                          <a class="notification-name-link" href="{{ route('leads.show', $followup->lead_id) }}">{{ $followup->lead->name ?? 'N/A' }}</a>
                        @endif
                      </td>
                      <td>{{ optional($notificationDueAt)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>{{ optional($notificationDueAt)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['follow_up'] }}">See More</a>
          </div>
        </div>
      </div>
    @endif

    @if($hasWebLeadNotifications)
      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-quick-leads" aria-expanded="false">
          <span>Quick Leads</span>
          <span class="count">{{ $webLeadNotificationCounts['quick_lead'] ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-quick-leads">
          @if ($quickLeads->isEmpty())
            <div class="text-center p-4 text-muted">No quick leads notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($quickLeads->take(3) as $webLead)
                    <tr>
                      <td>
                        @if(!empty($webLead->is_placeholder))
                          <span class="notification-name-link">{{ $webLead->full_name }}</span>
                          <div class="text-muted">{{ $webLead->city ?? 'City' }} - {{ $webLead->interested_program ?? 'Program' }}</div>
                        @else
                          <a class="notification-name-link" href="{{ route('web-leads.show', $webLead) }}">{{ $webLead->full_name }}</a>
                        @endif
                      </td>
                      <td>{{ optional($webLead->display_submitted_at)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>{{ optional($webLead->display_submitted_at)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['quick_lead'] }}">See More</a>
          </div>
        </div>
      </div>

      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-enrollments" aria-expanded="false">
          <span>Course Enrollment</span>
          <span class="count">{{ $webLeadNotificationCounts['website_enrollment'] ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-enrollments">
          @if ($websiteEnrollments->isEmpty())
            <div class="text-center p-4 text-muted">No course enrollment notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($websiteEnrollments->take(3) as $webLead)
                    <tr>
                      <td>
                        @if(!empty($webLead->is_placeholder))
                          <span class="notification-name-link">{{ $webLead->full_name }}</span>
                          <div class="text-muted">{{ $webLead->batch ?? 'Batch Pending' }} - {{ $webLead->status ?? 'New' }}</div>
                        @else
                          <a class="notification-name-link" href="{{ route('web-leads.show', $webLead) }}">{{ $webLead->full_name }}</a>
                        @endif
                      </td>
                      <td>{{ optional($webLead->display_submitted_at)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>{{ optional($webLead->display_submitted_at)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['website_enrollment'] }}">See More</a>
          </div>
        </div>
      </div>

      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-admissions" aria-expanded="false">
          <span>Website Admissions</span>
          <span class="count">{{ $webLeadNotificationCounts['website_admission'] ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-admissions">
          @if ($websiteAdmissions->isEmpty())
            <div class="text-center p-4 text-muted">No website admissions notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($websiteAdmissions->take(3) as $webLead)
                    <tr>
                      <td>
                        @if(!empty($webLead->is_placeholder))
                          <span class="notification-name-link">{{ $webLead->full_name }}</span>
                          <div class="text-muted">{{ $webLead->city ?? 'City' }} - {{ $webLead->interested_program ?? 'Program' }}</div>
                        @else
                          <a class="notification-name-link" href="{{ route('web-leads.show', $webLead) }}">{{ $webLead->full_name }}</a>
                        @endif
                      </td>
                      <td>{{ optional($webLead->display_submitted_at)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>{{ optional($webLead->display_submitted_at)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['website_admission'] }}">See More</a>
          </div>
        </div>
      </div>

      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-brochures" aria-expanded="false">
          <span>Brochure Downloads</span>
          <span class="count">{{ $webLeadNotificationCounts['brochure_download'] ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-brochures">
          @if ($brochureDownloads->isEmpty())
            <div class="text-center p-4 text-muted">No brochure download notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($brochureDownloads->take(3) as $webLead)
                    <tr>
                      <td>
                        @if(!empty($webLead->is_placeholder))
                          <span class="notification-name-link">{{ $webLead->full_name }}</span>
                          <div class="text-muted">{{ $webLead->city ?? 'City' }} - {{ $webLead->interested_program ?? 'Program' }}</div>
                        @else
                          <a class="notification-name-link" href="{{ route('web-leads.show', $webLead) }}">{{ $webLead->full_name }}</a>
                        @endif
                      </td>
                      <td>{{ optional($webLead->display_submitted_at)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>{{ optional($webLead->display_submitted_at)->format('h:i A') ?? 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['brochure_download'] }}">See More</a>
          </div>
        </div>
      </div>
    @endif

    @if($hasCoworkingDueNotifications)
      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-coworking-due" aria-expanded="false">
          <span>Coworking Due Soon</span>
          <span class="count">{{ $coworkingDueNotificationCount ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-coworking-due">
          @if ($coworkingDueItems->isEmpty())
            <div class="text-center p-4 text-muted">No coworking payments due in the next five days.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Member</th>
                    <th>Due Date</th>
                    <th>Charge</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($coworkingDueItems->take(3) as $registration)
                    <tr>
                      <td>
                        <a class="notification-name-link" href="{{ route('coworking-registrations.show', $registration) }}">
                          {{ $registration->full_name }}
                        </a>
                        <div class="text-muted">{{ $registration->registration_number ?: ($registration->campus->code ?? 'N/A') }}</div>
                      </td>
                      <td>{{ optional($registration->next_due_date)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>Rs. {{ number_format((float) $registration->coworking_charges, 0) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    @endif

    @if($hasInvoiceNotifications)
      <div class="notif-accordion-item notif-hover-card">
        <button class="notif-accordion-toggle" type="button" data-target="#notif-overdue-invoices" aria-expanded="false">
          <span>Overdue Invoices</span>
          <span class="count">{{ $invoiceOverdueNotificationCount ?? 0 }}</span>
        </button>
        <div class="notif-accordion-panel" id="notif-overdue-invoices">
          @if ($overdueInvoices->isEmpty())
            <div class="text-center p-4 text-muted">No overdue invoice notifications.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm mb-0 notification-table">
                <thead>
                  <tr>
                    <th>Invoice</th>
                    <th>Due Date</th>
                    <th>Balance</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($overdueInvoices->take(3) as $invoice)
                    <tr>
                      <td>
                        @if(!empty($invoice->is_placeholder))
                          <span class="notification-name-link">{{ $invoice->invoice_number ?: 'Invoice' }}</span>
                        @else
                          <a class="notification-name-link" href="{{ route('finance.receivables.show', $invoice) }}">
                            {{ $invoice->invoice_number ?: 'Invoice' }}
                          </a>
                        @endif
                        <div class="text-muted">{{ $invoice->student_name ?: ($invoice->campus->code ?? 'N/A') }}</div>
                      </td>
                      <td>{{ optional($invoice->due_date)->format('d-M-y') ?? 'N/A' }}</td>
                      <td>Rs. {{ number_format((float) $invoice->balance_amount, 0) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          <div class="notification-see-more">
            <a href="{{ $notificationMoreLinks['overdue_invoices'] }}">See More</a>
          </div>
        </div>
      </div>
    @endif
  </div>
@endif
