<?php

namespace Database\Seeders;

use App\Models\WebLead;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WebLeadSeeder extends Seeder
{
    public function run(): void
    {
        $rows = array_merge(
            $this->quickLeads(),
            $this->courseEnrollments(),
            $this->websiteAdmissions(),
            $this->brochureDownloads()
        );

        foreach ($rows as $row) {
            WebLead::updateOrCreate(
                [
                    'source_type' => $row['source_type'],
                    'email' => $row['email'],
                ],
                [
                    ...$row,
                    'source_site' => 'career.edu.pk',
                    'country' => 'Pakistan',
                    'status' => WebLead::STATUS_NEW,
                    'payload' => [
                        'source_type' => $row['source_type'],
                        'name' => $row['full_name'],
                        'email' => $row['email'],
                        'phone' => $row['phone'],
                        'country' => 'Pakistan',
                        'city' => $row['city'],
                        'area' => $row['area'],
                        'program' => $row['interested_program'],
                        'campus' => $row['preferred_campus'],
                        'teaching_method' => $row['teaching_method'],
                        'gender' => $row['gender'],
                        'message' => $row['message'],
                        ...($row['payload'] ?? []),
                    ],
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function quickLeads(): array
    {
        return $this->mapRows(WebLead::SOURCE_QUICK_LEAD, [
            ['Ayan Farooq', '0301-5550101', 'ayan.farooq@example.test', 'Lahore', 'Johar Town', 'Full Stack Web Development', 'LHR-Gulberg', 'campus', 'male', 'Asked for weekend batch fee and duration.'],
            ['Maira Siddiqui', '0302-5550102', 'maira.siddiqui@example.test', 'Karachi', 'Gulshan-e-Iqbal', 'Digital Marketing', 'KHI-Gulshan', 'online', 'female', 'Wants a counselor call after 4 PM.'],
            ['Haris Nadeem', '0303-5550103', 'haris.nadeem@example.test', 'Islamabad', 'G-10', 'Data Analytics', 'ISB-Blue Area', 'hybrid', 'male', 'Requested course outline and tools list.'],
            ['Zoya Kamran', '0304-5550104', 'zoya.kamran@example.test', 'Faisalabad', 'Civil Lines', 'Graphic Design', 'FSD-Civil Lines', 'campus', 'female', 'Interested in evening female batch.'],
            ['Taha Rehman', '0305-5550105', 'taha.rehman@example.test', 'Multan', 'Cantt', 'Python Programming', 'MUX-Cantt', 'online', 'male', 'Asked about beginner-friendly classes.'],
            ['Nimra Qureshi', '0306-5550106', 'nimra.qureshi@example.test', 'Rawalpindi', 'Satellite Town', 'UI UX Design', 'RWP-Satellite Town', 'hybrid', 'female', 'Needs portfolio guidance before enrollment.'],
            ['Daniyal Shah', '0307-5550107', 'daniyal.shah@example.test', 'Peshawar', 'University Road', 'Cyber Security', 'PEW-University Road', 'campus', 'male', 'Asked for lab availability and certification path.'],
            ['Anabia Malik', '0308-5550108', 'anabia.malik@example.test', 'Sialkot', 'Cantt', 'Spoken English', 'SKT-Cantt', 'campus', 'female', 'Requested placement test details.'],
            ['Saad Yousaf', '0309-5550109', 'saad.yousaf@example.test', 'Gujranwala', 'GT Road', 'Amazon FBA', 'GUJ-GT Road', 'online', 'male', 'Needs installment plan information.'],
            ['Hania Tariq', '0310-5550110', 'hania.tariq@example.test', 'Hyderabad', 'Latifabad', 'Office Management', 'HYD-Latifabad', 'campus', 'female', 'Asked for morning batch timings.'],
            ['Bilal Azeem', '0311-5550111', 'bilal.azeem@example.test', 'Quetta', 'Jinnah Road', 'Video Editing', 'QTA-Jinnah Road', 'hybrid', 'male', 'Wants demo class link.'],
            ['Rida Salman', '0312-5550112', 'rida.salman@example.test', 'Bahawalpur', 'Model Town', 'AI Essentials', 'BWP-Model Town', 'online', 'female', 'Asked if laptop is required.'],
        ], Carbon::parse('2026-07-01 09:10:00'), 'quick_lead');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function courseEnrollments(): array
    {
        return $this->mapRows(WebLead::SOURCE_WEBSITE_ENROLLMENT, [
            ['Sarmad Iqbal', '0321-5550201', 'sarmad.iqbal@example.test', 'Lahore', 'Johar Town', 'MERN Stack', 'LHR-Johar Town', 'campus', 'male', 'Submitted course enrollment form.', 'MERN-0726-A', 'Document Review'],
            ['Eshal Noor', '0322-5550202', 'eshal.noor@example.test', 'Karachi', 'Gulshan', 'Social Media Marketing', 'KHI-Gulshan', 'online', 'female', 'Requested payment link.', 'SMM-0726-B', 'Fee Pending'],
            ['Muneeb Khalid', '0323-5550203', 'muneeb.khalid@example.test', 'Islamabad', 'F-10', 'Cloud Computing', 'ISB-F10', 'hybrid', 'male', 'Confirmed batch preference.', 'CLD-0726-A', 'Confirmed'],
            ['Laiba Rauf', '0324-5550204', 'laiba.rauf@example.test', 'Faisalabad', 'D Ground', 'AutoCAD', 'FSD-D Ground', 'campus', 'female', 'Awaiting CNIC copy.', 'CAD-0726-C', 'Counselor Assigned'],
            ['Hamza Javed', '0325-5550205', 'hamza.javed@example.test', 'Multan', 'Gulgasht', 'WordPress Development', 'MUX-Gulgasht', 'online', 'male', 'Enrollment submitted from website.', 'WPD-0726-A', 'Confirmed'],
            ['Mahnoor Ilyas', '0326-5550206', 'mahnoor.ilyas@example.test', 'Rawalpindi', 'Saddar', 'Business Communication', 'RWP-Saddar', 'campus', 'female', 'Needs invoice before confirmation.', 'BCM-0726-B', 'Fee Pending'],
            ['Usman Rafiq', '0327-5550207', 'usman.rafiq@example.test', 'Peshawar', 'Cantt', 'Network Administration', 'PEW-Cantt', 'hybrid', 'male', 'Uploaded prior certificate.', 'NET-0726-A', 'Document Review'],
            ['Areeba Sohail', '0328-5550208', 'areeba.sohail@example.test', 'Sialkot', 'Paris Road', 'Fashion Design', 'SKT-Paris Road', 'campus', 'female', 'Confirmed visit for enrollment.', 'FSD-0726-A', 'Confirmed'],
            ['Noman Aslam', '0329-5550209', 'noman.aslam@example.test', 'Gujrat', 'Shadman', 'Search Engine Optimization', 'GRT-Shadman', 'online', 'male', 'Needs counselor assignment.', 'SEO-0726-D', 'Counselor Assigned'],
            ['Iqra Waseem', '0330-5550210', 'iqra.waseem@example.test', 'Hyderabad', 'Qasimabad', 'Content Writing', 'HYD-Qasimabad', 'online', 'female', 'Enrollment confirmed by website form.', 'CNT-0726-B', 'Confirmed'],
            ['Kashif Mehmood', '0331-5550211', 'kashif.mehmood@example.test', 'Abbottabad', 'Main Road', 'CCNA', 'ABT-Main Road', 'campus', 'male', 'Awaiting fee confirmation.', 'CCNA-0726-A', 'Fee Pending'],
            ['Sana Irfan', '0332-5550212', 'sana.irfan@example.test', 'Sukkur', 'Military Road', 'Advanced Excel', 'SKR-Military Road', 'hybrid', 'female', 'Document review required.', 'XLS-0726-C', 'Document Review'],
        ], Carbon::parse('2026-06-30 10:10:00'), 'course_enrollment');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function websiteAdmissions(): array
    {
        return $this->mapRows(WebLead::SOURCE_WEBSITE_ADMISSION, [
            ['Rayyan Sheikh', '0341-5550301', 'rayyan.sheikh@example.test', 'Lahore', 'Model Town', 'Software Engineering Diploma', 'LHR-Main', 'campus', 'male', 'Submitted admission inquiry from website.'],
            ['Alina Zahid', '0342-5550302', 'alina.zahid@example.test', 'Karachi', 'Clifton', 'Professional Accounting', 'KHI-Clifton', 'online', 'female', 'Asked for admission eligibility.'],
            ['Fahad Munir', '0343-5550303', 'fahad.munir@example.test', 'Islamabad', 'F-8', 'Project Management', 'ISB-F8', 'hybrid', 'male', 'Needs document checklist.'],
            ['Minahil Saleem', '0344-5550304', 'minahil.saleem@example.test', 'Faisalabad', 'Madina Town', 'Textile Design', 'FSD-Madina Town', 'campus', 'female', 'Asked about admission deadline.'],
            ['Adeel Nawaz', '0345-5550305', 'adeel.nawaz@example.test', 'Multan', 'Gulgasht', 'E-Commerce Management', 'MUX-Main', 'online', 'male', 'Requested fee voucher details.'],
            ['Noor ul Ain', '0346-5550306', 'noor.ain@example.test', 'Rawalpindi', 'Murree Road', 'Computerized Accounting', 'RWP-Murree Road', 'campus', 'female', 'Asked for admission appointment.'],
            ['Arham Butt', '0347-5550307', 'arham.butt@example.test', 'Peshawar', 'University Town', 'Mobile App Development', 'PEW-Main', 'hybrid', 'male', 'Submitted inquiry for next intake.'],
            ['Tania Ahmed', '0348-5550308', 'tania.ahmed@example.test', 'Sialkot', 'Cantt', 'IELTS Preparation', 'SKT-Main', 'campus', 'female', 'Asked for admission test schedule.'],
            ['Junaid Akhtar', '0349-5550309', 'junaid.akhtar@example.test', 'Gujranwala', 'Satellite Town', 'Freelancing Bootcamp', 'GUJ-Main', 'online', 'male', 'Requested online admission process.'],
            ['Hira Naveed', '0350-5550310', 'hira.naveed@example.test', 'Hyderabad', 'Latifabad', 'HR Management', 'HYD-Main', 'campus', 'female', 'Needs admission confirmation call.'],
            ['Shehroz Ali', '0351-5550311', 'shehroz.ali@example.test', 'Quetta', 'Jinnah Road', 'Database Administration', 'QTA-Main', 'hybrid', 'male', 'Asked about prerequisite knowledge.'],
            ['Mehak Feroz', '0352-5550312', 'mehak.feroz@example.test', 'Bahawalpur', 'Model Town', 'Montessori Teaching', 'BWP-Main', 'campus', 'female', 'Submitted inquiry for weekend class.'],
        ], Carbon::parse('2026-06-29 09:20:00'), 'website_admission');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function brochureDownloads(): array
    {
        return $this->mapRows(WebLead::SOURCE_BROCHURE_DOWNLOAD, [
            ['Kiran Abbas', '0361-5550401', 'kiran.abbas@example.test', 'Lahore', 'DHA', 'AI Essentials', 'LHR-Main', 'online', 'female', 'Downloaded AI Essentials brochure.'],
            ['Shayan Malik', '0362-5550402', 'shayan.malik@example.test', 'Karachi', 'Saddar', 'Digital Marketing', 'KHI-Main', 'online', 'male', 'Downloaded digital marketing brochure.'],
            ['Maryam Faisal', '0363-5550403', 'maryam.faisal@example.test', 'Islamabad', 'G-9', 'Data Science', 'ISB-Main', 'hybrid', 'female', 'Requested data science prospectus.'],
            ['Osama Latif', '0364-5550404', 'osama.latif@example.test', 'Faisalabad', 'People Colony', 'Graphic Design', 'FSD-Main', 'campus', 'male', 'Downloaded design brochure.'],
            ['Ayesha Wajid', '0365-5550405', 'ayesha.wajid@example.test', 'Multan', 'Cantt', 'Spoken English', 'MUX-Main', 'campus', 'female', 'Downloaded English brochure.'],
            ['Zain Ul Haq', '0366-5550406', 'zain.haq@example.test', 'Rawalpindi', 'Bahria Town', 'Cyber Security', 'RWP-Main', 'hybrid', 'male', 'Downloaded cyber security brochure.'],
            ['Hafsa Jamil', '0367-5550407', 'hafsa.jamil@example.test', 'Peshawar', 'Hayatabad', 'Python Programming', 'PEW-Main', 'online', 'female', 'Downloaded Python course brochure.'],
            ['Talha Naseer', '0368-5550408', 'talha.naseer@example.test', 'Sialkot', 'Paris Road', 'Video Editing', 'SKT-Main', 'campus', 'male', 'Requested editing course PDF.'],
            ['Sadia Aamir', '0369-5550409', 'sadia.aamir@example.test', 'Gujranwala', 'GT Road', 'Amazon FBA', 'GUJ-Main', 'online', 'female', 'Downloaded Amazon FBA brochure.'],
            ['Moiz Hassan', '0370-5550410', 'moiz.hassan@example.test', 'Hyderabad', 'Qasimabad', 'Office Automation', 'HYD-Main', 'campus', 'male', 'Downloaded office automation brochure.'],
            ['Fiza Junaid', '0371-5550411', 'fiza.junaid@example.test', 'Quetta', 'Cantt', 'UI UX Design', 'QTA-Main', 'hybrid', 'female', 'Downloaded UI UX brochure.'],
            ['Sameer Raza', '0372-5550412', 'sameer.raza@example.test', 'Bahawalpur', 'Satellite Town', 'Cloud Computing', 'BWP-Main', 'online', 'male', 'Downloaded cloud computing brochure.'],
        ], Carbon::parse('2026-06-28 08:45:00'), 'brochure_download');
    }

    /**
     * @param array<int, array<int, string>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function mapRows(string $sourceType, array $rows, Carbon $startAt, string $module): array
    {
        return collect($rows)
            ->map(function (array $row, int $index) use ($sourceType, $startAt, $module) {
                $submittedAt = $startAt->copy()->addMinutes($index * 35);

                return [
                    'source_type' => $sourceType,
                    'full_name' => $row[0],
                    'phone' => $row[1],
                    'email' => $row[2],
                    'city' => $row[3],
                    'area' => $row[4],
                    'interested_program' => $row[5],
                    'preferred_campus' => $row[6],
                    'teaching_method' => $row[7],
                    'gender' => $row[8],
                    'message' => $row[9],
                    'submitted_at' => $submittedAt,
                    'payload' => [
                        'module' => $module,
                        'batch' => $row[10] ?? null,
                        'module_status' => $row[11] ?? 'New',
                        'display_date' => $submittedAt->toDateString(),
                    ],
                ];
            })
            ->all();
    }
}
