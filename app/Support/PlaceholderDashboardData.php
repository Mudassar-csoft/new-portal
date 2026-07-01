<?php

namespace App\Support;

use App\Models\WebLead;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use stdClass;

class PlaceholderDashboardData
{
    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function webLeadSections(): array
    {
        return [
            WebLead::SOURCE_QUICK_LEAD => [
                ['full_name' => 'Ayan Farooq', 'phone' => '0301-5550101', 'email' => 'ayan.farooq@example.test', 'city' => 'Lahore', 'interested_program' => 'Full Stack Web Development', 'preferred_campus' => 'LHR-Gulberg', 'submitted_at' => '2026-07-01 09:10:00'],
                ['full_name' => 'Maira Siddiqui', 'phone' => '0302-5550102', 'email' => 'maira.siddiqui@example.test', 'city' => 'Karachi', 'interested_program' => 'Digital Marketing', 'preferred_campus' => 'KHI-Saddar', 'submitted_at' => '2026-07-01 09:35:00'],
                ['full_name' => 'Haris Nadeem', 'phone' => '0303-5550103', 'email' => 'haris.nadeem@example.test', 'city' => 'Islamabad', 'interested_program' => 'Data Analytics', 'preferred_campus' => 'ISB-Blue Area', 'submitted_at' => '2026-07-01 10:05:00'],
                ['full_name' => 'Zoya Kamran', 'phone' => '0304-5550104', 'email' => 'zoya.kamran@example.test', 'city' => 'Faisalabad', 'interested_program' => 'Graphic Design', 'preferred_campus' => 'FSD-Civil Lines', 'submitted_at' => '2026-07-01 10:40:00'],
                ['full_name' => 'Taha Rehman', 'phone' => '0305-5550105', 'email' => 'taha.rehman@example.test', 'city' => 'Multan', 'interested_program' => 'Python Programming', 'preferred_campus' => 'MUX-Cantt', 'submitted_at' => '2026-07-01 11:15:00'],
                ['full_name' => 'Nimra Qureshi', 'phone' => '0306-5550106', 'email' => 'nimra.qureshi@example.test', 'city' => 'Rawalpindi', 'interested_program' => 'UI UX Design', 'preferred_campus' => 'RWP-Satellite Town', 'submitted_at' => '2026-07-01 11:45:00'],
                ['full_name' => 'Daniyal Shah', 'phone' => '0307-5550107', 'email' => 'daniyal.shah@example.test', 'city' => 'Peshawar', 'interested_program' => 'Cyber Security', 'preferred_campus' => 'PEW-University Road', 'submitted_at' => '2026-07-01 12:20:00'],
                ['full_name' => 'Anabia Malik', 'phone' => '0308-5550108', 'email' => 'anabia.malik@example.test', 'city' => 'Sialkot', 'interested_program' => 'Spoken English', 'preferred_campus' => 'SKT-Cantt', 'submitted_at' => '2026-07-01 12:50:00'],
                ['full_name' => 'Saad Yousaf', 'phone' => '0309-5550109', 'email' => 'saad.yousaf@example.test', 'city' => 'Gujranwala', 'interested_program' => 'Amazon FBA', 'preferred_campus' => 'GUJ-GT Road', 'submitted_at' => '2026-07-01 13:25:00'],
                ['full_name' => 'Hania Tariq', 'phone' => '0310-5550110', 'email' => 'hania.tariq@example.test', 'city' => 'Hyderabad', 'interested_program' => 'Office Management', 'preferred_campus' => 'HYD-Latifabad', 'submitted_at' => '2026-07-01 14:05:00'],
                ['full_name' => 'Bilal Azeem', 'phone' => '0311-5550111', 'email' => 'bilal.azeem@example.test', 'city' => 'Quetta', 'interested_program' => 'Video Editing', 'preferred_campus' => 'QTA-Jinnah Road', 'submitted_at' => '2026-07-01 14:40:00'],
                ['full_name' => 'Rida Salman', 'phone' => '0312-5550112', 'email' => 'rida.salman@example.test', 'city' => 'Bahawalpur', 'interested_program' => 'AI Essentials', 'preferred_campus' => 'BWP-Model Town', 'submitted_at' => '2026-07-01 15:10:00'],
            ],
            WebLead::SOURCE_WEBSITE_ENROLLMENT => [
                ['full_name' => 'Sarmad Iqbal', 'phone' => '0321-5550201', 'email' => 'sarmad.iqbal@example.test', 'city' => 'Lahore', 'interested_program' => 'MERN Stack', 'preferred_campus' => 'LHR-Johar Town', 'submitted_at' => '2026-06-30 10:10:00', 'batch' => 'MERN-0726-A', 'status' => 'Document Review'],
                ['full_name' => 'Eshal Noor', 'phone' => '0322-5550202', 'email' => 'eshal.noor@example.test', 'city' => 'Karachi', 'interested_program' => 'Social Media Marketing', 'preferred_campus' => 'KHI-Gulshan', 'submitted_at' => '2026-06-30 10:35:00', 'batch' => 'SMM-0726-B', 'status' => 'Fee Pending'],
                ['full_name' => 'Muneeb Khalid', 'phone' => '0323-5550203', 'email' => 'muneeb.khalid@example.test', 'city' => 'Islamabad', 'interested_program' => 'Cloud Computing', 'preferred_campus' => 'ISB-F10', 'submitted_at' => '2026-06-30 11:00:00', 'batch' => 'CLD-0726-A', 'status' => 'Confirmed'],
                ['full_name' => 'Laiba Rauf', 'phone' => '0324-5550204', 'email' => 'laiba.rauf@example.test', 'city' => 'Faisalabad', 'interested_program' => 'AutoCAD', 'preferred_campus' => 'FSD-D Ground', 'submitted_at' => '2026-06-30 11:45:00', 'batch' => 'CAD-0726-C', 'status' => 'Counselor Assigned'],
                ['full_name' => 'Hamza Javed', 'phone' => '0325-5550205', 'email' => 'hamza.javed@example.test', 'city' => 'Multan', 'interested_program' => 'WordPress Development', 'preferred_campus' => 'MUX-Gulgasht', 'submitted_at' => '2026-06-30 12:15:00', 'batch' => 'WPD-0726-A', 'status' => 'Confirmed'],
                ['full_name' => 'Mahnoor Ilyas', 'phone' => '0326-5550206', 'email' => 'mahnoor.ilyas@example.test', 'city' => 'Rawalpindi', 'interested_program' => 'Business Communication', 'preferred_campus' => 'RWP-Saddar', 'submitted_at' => '2026-06-30 12:45:00', 'batch' => 'BCM-0726-B', 'status' => 'Fee Pending'],
                ['full_name' => 'Usman Rafiq', 'phone' => '0327-5550207', 'email' => 'usman.rafiq@example.test', 'city' => 'Peshawar', 'interested_program' => 'Network Administration', 'preferred_campus' => 'PEW-Cantt', 'submitted_at' => '2026-06-30 13:20:00', 'batch' => 'NET-0726-A', 'status' => 'Document Review'],
                ['full_name' => 'Areeba Sohail', 'phone' => '0328-5550208', 'email' => 'areeba.sohail@example.test', 'city' => 'Sialkot', 'interested_program' => 'Fashion Design', 'preferred_campus' => 'SKT-Paris Road', 'submitted_at' => '2026-06-30 13:50:00', 'batch' => 'FSD-0726-A', 'status' => 'Confirmed'],
                ['full_name' => 'Noman Aslam', 'phone' => '0329-5550209', 'email' => 'noman.aslam@example.test', 'city' => 'Gujrat', 'interested_program' => 'Search Engine Optimization', 'preferred_campus' => 'GRT-Shadman', 'submitted_at' => '2026-06-30 14:30:00', 'batch' => 'SEO-0726-D', 'status' => 'Counselor Assigned'],
                ['full_name' => 'Iqra Waseem', 'phone' => '0330-5550210', 'email' => 'iqra.waseem@example.test', 'city' => 'Hyderabad', 'interested_program' => 'Content Writing', 'preferred_campus' => 'HYD-Qasimabad', 'submitted_at' => '2026-06-30 15:00:00', 'batch' => 'CNT-0726-B', 'status' => 'Confirmed'],
                ['full_name' => 'Kashif Mehmood', 'phone' => '0331-5550211', 'email' => 'kashif.mehmood@example.test', 'city' => 'Abbottabad', 'interested_program' => 'CCNA', 'preferred_campus' => 'ABT-Main Road', 'submitted_at' => '2026-06-30 15:35:00', 'batch' => 'CCNA-0726-A', 'status' => 'Fee Pending'],
                ['full_name' => 'Sana Irfan', 'phone' => '0332-5550212', 'email' => 'sana.irfan@example.test', 'city' => 'Sukkur', 'interested_program' => 'Advanced Excel', 'preferred_campus' => 'SKR-Military Road', 'submitted_at' => '2026-06-30 16:05:00', 'batch' => 'XLS-0726-C', 'status' => 'Document Review'],
            ],
            WebLead::SOURCE_WEBSITE_ADMISSION => [
                ['full_name' => 'Rayyan Sheikh', 'phone' => '0341-5550301', 'email' => 'rayyan.sheikh@example.test', 'city' => 'Lahore', 'interested_program' => 'Software Engineering Diploma', 'preferred_campus' => 'LHR-Main', 'submitted_at' => '2026-06-29 09:20:00'],
                ['full_name' => 'Alina Zahid', 'phone' => '0342-5550302', 'email' => 'alina.zahid@example.test', 'city' => 'Karachi', 'interested_program' => 'Professional Accounting', 'preferred_campus' => 'KHI-Clifton', 'submitted_at' => '2026-06-29 09:55:00'],
                ['full_name' => 'Fahad Munir', 'phone' => '0343-5550303', 'email' => 'fahad.munir@example.test', 'city' => 'Islamabad', 'interested_program' => 'Project Management', 'preferred_campus' => 'ISB-F8', 'submitted_at' => '2026-06-29 10:30:00'],
                ['full_name' => 'Minahil Saleem', 'phone' => '0344-5550304', 'email' => 'minahil.saleem@example.test', 'city' => 'Faisalabad', 'interested_program' => 'Textile Design', 'preferred_campus' => 'FSD-Madina Town', 'submitted_at' => '2026-06-29 11:00:00'],
                ['full_name' => 'Adeel Nawaz', 'phone' => '0345-5550305', 'email' => 'adeel.nawaz@example.test', 'city' => 'Multan', 'interested_program' => 'E-Commerce Management', 'preferred_campus' => 'MUX-Main', 'submitted_at' => '2026-06-29 11:40:00'],
                ['full_name' => 'Noor ul Ain', 'phone' => '0346-5550306', 'email' => 'noor.ain@example.test', 'city' => 'Rawalpindi', 'interested_program' => 'Computerized Accounting', 'preferred_campus' => 'RWP-Murree Road', 'submitted_at' => '2026-06-29 12:10:00'],
                ['full_name' => 'Arham Butt', 'phone' => '0347-5550307', 'email' => 'arham.butt@example.test', 'city' => 'Peshawar', 'interested_program' => 'Mobile App Development', 'preferred_campus' => 'PEW-Main', 'submitted_at' => '2026-06-29 12:45:00'],
                ['full_name' => 'Tania Ahmed', 'phone' => '0348-5550308', 'email' => 'tania.ahmed@example.test', 'city' => 'Sialkot', 'interested_program' => 'IELTS Preparation', 'preferred_campus' => 'SKT-Main', 'submitted_at' => '2026-06-29 13:25:00'],
                ['full_name' => 'Junaid Akhtar', 'phone' => '0349-5550309', 'email' => 'junaid.akhtar@example.test', 'city' => 'Gujranwala', 'interested_program' => 'Freelancing Bootcamp', 'preferred_campus' => 'GUJ-Main', 'submitted_at' => '2026-06-29 13:55:00'],
                ['full_name' => 'Hira Naveed', 'phone' => '0350-5550310', 'email' => 'hira.naveed@example.test', 'city' => 'Hyderabad', 'interested_program' => 'HR Management', 'preferred_campus' => 'HYD-Main', 'submitted_at' => '2026-06-29 14:30:00'],
                ['full_name' => 'Shehroz Ali', 'phone' => '0351-5550311', 'email' => 'shehroz.ali@example.test', 'city' => 'Quetta', 'interested_program' => 'Database Administration', 'preferred_campus' => 'QTA-Main', 'submitted_at' => '2026-06-29 15:05:00'],
                ['full_name' => 'Mehak Feroz', 'phone' => '0352-5550312', 'email' => 'mehak.feroz@example.test', 'city' => 'Bahawalpur', 'interested_program' => 'Montessori Teaching', 'preferred_campus' => 'BWP-Main', 'submitted_at' => '2026-06-29 15:45:00'],
            ],
            WebLead::SOURCE_BROCHURE_DOWNLOAD => [
                ['full_name' => 'Kiran Abbas', 'phone' => '0361-5550401', 'email' => 'kiran.abbas@example.test', 'city' => 'Lahore', 'interested_program' => 'AI Essentials', 'preferred_campus' => 'LHR-Main', 'submitted_at' => '2026-06-28 08:45:00'],
                ['full_name' => 'Shayan Malik', 'phone' => '0362-5550402', 'email' => 'shayan.malik@example.test', 'city' => 'Karachi', 'interested_program' => 'Digital Marketing', 'preferred_campus' => 'KHI-Main', 'submitted_at' => '2026-06-28 09:15:00'],
                ['full_name' => 'Maryam Faisal', 'phone' => '0363-5550403', 'email' => 'maryam.faisal@example.test', 'city' => 'Islamabad', 'interested_program' => 'Data Science', 'preferred_campus' => 'ISB-Main', 'submitted_at' => '2026-06-28 09:50:00'],
                ['full_name' => 'Osama Latif', 'phone' => '0364-5550404', 'email' => 'osama.latif@example.test', 'city' => 'Faisalabad', 'interested_program' => 'Graphic Design', 'preferred_campus' => 'FSD-Main', 'submitted_at' => '2026-06-28 10:20:00'],
                ['full_name' => 'Ayesha Wajid', 'phone' => '0365-5550405', 'email' => 'ayesha.wajid@example.test', 'city' => 'Multan', 'interested_program' => 'Spoken English', 'preferred_campus' => 'MUX-Main', 'submitted_at' => '2026-06-28 10:55:00'],
                ['full_name' => 'Zain Ul Haq', 'phone' => '0366-5550406', 'email' => 'zain.haq@example.test', 'city' => 'Rawalpindi', 'interested_program' => 'Cyber Security', 'preferred_campus' => 'RWP-Main', 'submitted_at' => '2026-06-28 11:25:00'],
                ['full_name' => 'Hafsa Jamil', 'phone' => '0367-5550407', 'email' => 'hafsa.jamil@example.test', 'city' => 'Peshawar', 'interested_program' => 'Python Programming', 'preferred_campus' => 'PEW-Main', 'submitted_at' => '2026-06-28 12:00:00'],
                ['full_name' => 'Talha Naseer', 'phone' => '0368-5550408', 'email' => 'talha.naseer@example.test', 'city' => 'Sialkot', 'interested_program' => 'Video Editing', 'preferred_campus' => 'SKT-Main', 'submitted_at' => '2026-06-28 12:35:00'],
                ['full_name' => 'Sadia Aamir', 'phone' => '0369-5550409', 'email' => 'sadia.aamir@example.test', 'city' => 'Gujranwala', 'interested_program' => 'Amazon FBA', 'preferred_campus' => 'GUJ-Main', 'submitted_at' => '2026-06-28 13:10:00'],
                ['full_name' => 'Moiz Hassan', 'phone' => '0370-5550410', 'email' => 'moiz.hassan@example.test', 'city' => 'Hyderabad', 'interested_program' => 'Office Automation', 'preferred_campus' => 'HYD-Main', 'submitted_at' => '2026-06-28 13:45:00'],
                ['full_name' => 'Fiza Junaid', 'phone' => '0371-5550411', 'email' => 'fiza.junaid@example.test', 'city' => 'Quetta', 'interested_program' => 'UI UX Design', 'preferred_campus' => 'QTA-Main', 'submitted_at' => '2026-06-28 14:25:00'],
                ['full_name' => 'Sameer Raza', 'phone' => '0372-5550412', 'email' => 'sameer.raza@example.test', 'city' => 'Bahawalpur', 'interested_program' => 'Cloud Computing', 'preferred_campus' => 'BWP-Main', 'submitted_at' => '2026-06-28 15:05:00'],
            ],
        ];
    }

    public static function followups(): Collection
    {
        $statuses = ['Pending', 'Call Back', 'Visit Planned', 'Proposal Sent', 'Awaiting Documents', 'Counselor Review'];
        $counselors = ['Sara Iqbal', 'Omer Farid', 'Nida Hassan', 'Bilal Qureshi', 'Amina Raza', 'Danish Mir'];

        return collect(self::webLeadSections()[WebLead::SOURCE_QUICK_LEAD])
            ->values()
            ->map(function (array $lead, int $index) use ($statuses, $counselors) {
                $dueAt = Carbon::parse('2026-07-01 09:00:00')->addHours($index * 2);

                return (object) [
                    'id' => 9000 + $index,
                    'lead_id' => 12000 + $index,
                    'notification_due_at' => $dueAt,
                    'next_action_date' => $dueAt,
                    'lead' => (object) [
                        'name' => $lead['full_name'],
                        'phone' => $lead['phone'],
                    ],
                    'counselor' => $counselors[$index % count($counselors)],
                    'status' => $statuses[$index % count($statuses)],
                    'is_placeholder' => true,
                ];
            });
    }

    public static function overdueInvoices(): Collection
    {
        $names = ['Rameen Asif', 'Faris Khan', 'Sadia Imran', 'Mikaal Tariq', 'Areej Bano', 'Hamid Raza', 'Kinza Noor', 'Rehan Siddique', 'Laiba Arif', 'Umair Bashir', 'Maham Iqbal', 'Arsalan Niaz'];

        return collect($names)->map(function (string $name, int $index) {
            return (object) [
                'id' => 8100 + $index,
                'invoice_number' => 'INV-26-' . str_pad((string) (1401 + $index), 4, '0', STR_PAD_LEFT),
                'student_name' => $name,
                'due_date' => Carbon::parse('2026-06-30')->subDays($index + 2),
                'balance_amount' => 18500 + ($index * 2750),
                'status' => 'Unpaid',
                'is_placeholder' => true,
            ];
        });
    }

    public static function webLeadCollection(?string $sourceType = null): Collection
    {
        $rows = collect(self::webLeadSections())
            ->flatMap(function (array $items, string $type) {
                return collect($items)->map(function (array $item, int $index) use ($type) {
                    return self::makeWebLeadObject($item, $type, $index);
                });
            })
            ->values();

        return $sourceType ? $rows->where('source_type', $sourceType)->values() : $rows;
    }

    public static function webLeadPaginator(string $activeTab, string $search, int $perPage, int $page): LengthAwarePaginator
    {
        $items = self::webLeadCollection($activeTab !== 'all' ? $activeTab : null);

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $items = $items->filter(function (stdClass $item) use ($needle) {
                return str_contains(mb_strtolower(implode(' ', [
                    $item->full_name,
                    $item->phone,
                    $item->email,
                    $item->city,
                    $item->interested_program,
                    $item->preferred_campus,
                ])), $needle);
            })->values();
        }

        if ($items->isEmpty()) {
            $items = self::webLeadCollection($activeTab !== 'all' ? $activeTab : null)->take(12)->values();
        }

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * @return array<string, int>
     */
    public static function webLeadCounts(): array
    {
        return collect(self::webLeadSections())
            ->map(fn (array $items) => count($items))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function dashboardPayload(): array
    {
        return [
            'stats' => [
                'todayLeads' => 18,
                'totalLeads' => 246,
                'currentStudents' => 184,
                'currentMonthAdmissions' => 42,
                'previousMonthAdmissions' => 37,
                'currentMonthCollection' => '1,286,500',
                'currentMonthCollectionRaw' => 1286500,
                'pendingRecoveryRaw' => 342000,
                'todayCollectionRaw' => 86500,
                'weekCollectionRaw' => 412800,
            ],
            'dailyActivity' => [
                'rows' => collect(self::webLeadSections()[WebLead::SOURCE_QUICK_LEAD])->map(function (array $lead, int $index) {
                    return [
                        'status_label' => ['New', 'Contacted', 'Follow Up', 'Registered'][$index % 4],
                        'status_tone' => ['info', 'primary', 'warning', 'success'][$index % 4],
                        'student_name' => $lead['full_name'],
                        'detail_url' => null,
                        'phone' => $lead['phone'],
                        'date_label' => Carbon::parse($lead['submitted_at'])->format('d-M-Y'),
                        'campus' => $lead['preferred_campus'],
                        'show_campus' => true,
                    ];
                })->values()->all(),
                'totals' => [
                    'leads' => 18,
                    'followups' => 12,
                    'admissions' => 9,
                    'collection' => 86500,
                ],
            ],
            'admissionsActivity' => [
                'rows' => collect(self::webLeadSections()[WebLead::SOURCE_WEBSITE_ADMISSION])->map(function (array $lead) {
                    return [
                        'status_label' => 'Enrolled',
                        'status_tone' => 'success',
                        'student_name' => $lead['full_name'],
                        'detail_url' => null,
                        'phone' => $lead['phone'],
                        'date_label' => Carbon::parse($lead['submitted_at'])->format('d-M-Y'),
                        'campus' => $lead['preferred_campus'],
                        'show_campus' => true,
                    ];
                })->values()->all(),
            ],
            'incomeSummary' => [
                'today' => 86500,
                'week' => 412800,
                'month' => 1286500,
            ],
            'incomeRanges' => [
                'today' => ['label' => 'Today income (hourly)', 'points' => [['08 AM', 9500], ['10 AM', 12500], ['12 PM', 18200], ['02 PM', 14600], ['04 PM', 20100], ['06 PM', 11600]], 'ticks' => [0, 5000, 10000, 15000, 20000, 25000]],
                'week' => ['label' => 'Week income (daily)', 'points' => [['Thu', 54200], ['Fri', 68800], ['Sat', 72100], ['Sun', 33100], ['Mon', 58400], ['Tue', 39800], ['Wed', 86500]], 'ticks' => [0, 20000, 40000, 60000, 80000, 100000]],
                'month' => ['label' => 'Month income (weekly)', 'points' => [['Week 1', 286000], ['Week 2', 318500], ['Week 3', 341000], ['Week 4', 341000]], 'ticks' => [0, 100000, 200000, 300000, 400000]],
                'year' => ['label' => 'Year income (monthly)', 'points' => [['Jan', 920000], ['Feb', 1015000], ['Mar', 1112000], ['Apr', 1186000], ['May', 1224000], ['Jun', 1286500]], 'ticks' => [0, 300000, 600000, 900000, 1200000, 1500000]],
            ],
            'charts' => [
                'leads' => ['categories' => ['AI', 'MERN', 'SEO', 'Python', 'Graphic', 'Cyber', 'IELTS', 'Excel', 'CCNA', 'CAD', 'Cloud', 'SMM'], 'counts' => [18, 16, 14, 13, 12, 10, 9, 8, 8, 7, 7, 6]],
                'admissions' => ['categories' => ['AI', 'MERN', 'SEO', 'Python', 'Graphic', 'Cyber', 'IELTS', 'Excel', 'CCNA', 'CAD', 'Cloud', 'SMM'], 'counts' => [7, 6, 5, 5, 4, 3, 3, 2, 2, 2, 2, 1]],
                'campusAdmissions' => ['categories' => ['LHR', 'KHI', 'ISB', 'FSD', 'MUX', 'RWP', 'PEW', 'SKT', 'GUJ', 'HYD', 'QTA', 'BWP'], 'counts' => [9, 7, 6, 5, 4, 3, 3, 2, 1, 1, 1, 1]],
            ],
            'monthlyAdmissionsInsight' => [
                'labels' => ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                'counts' => [24, 27, 29, 31, 28, 33, 35, 39, 36, 41, 37, 42],
                'current' => 42,
                'previous' => 37,
            ],
        ];
    }

    private static function makeWebLeadObject(array $item, string $sourceType, int $index): stdClass
    {
        return (object) [
            'id' => 7000 + ($index + 1),
            'source_type' => $sourceType,
            'source_label' => WebLead::sourceLabels()[$sourceType] ?? 'Web Lead',
            'full_name' => $item['full_name'],
            'phone' => $item['phone'],
            'email' => $item['email'],
            'city' => $item['city'],
            'interested_program' => $item['interested_program'],
            'preferred_campus' => $item['preferred_campus'],
            'campus_id' => $item['preferred_campus'],
            'submitted_at' => Carbon::parse($item['submitted_at']),
            'created_at' => Carbon::parse($item['submitted_at']),
            'status' => $item['status'] ?? WebLead::STATUS_NEW,
            'batch' => $item['batch'] ?? null,
            'is_placeholder' => true,
        ];
    }
}
