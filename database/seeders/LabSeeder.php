<?php

namespace Database\Seeders;

use App\Models\Lab;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    private const FASTING = 'Fast for 8-12 hours before the test. Water is allowed.';

    /**
     * Common lab tests, keyed by name so re-running only updates them.
     *
     * [name, arabic name, code, category, sample type, fasting required, preparation]
     */
    public function run(): void
    {
        foreach ($this->labs() as [$name, $nameAr, $code, $category, $sample, $fasting, $preparation]) {
            Lab::updateOrCreate(['name' => $name], [
                'name_ar' => $nameAr,
                'code' => $code,
                'category' => $category,
                'sample_type' => $sample,
                'fasting_required' => $fasting,
                'preparation' => $preparation ?? ($fasting ? self::FASTING : null),
                'is_active' => true,
            ]);
        }
    }

    private function labs(): array
    {
        return [
            // Hematology
            ['Complete Blood Count (CBC)', 'صورة دم كاملة', 'CBC', 'Hematology', 'Blood', false, null],
            ['Erythrocyte Sedimentation Rate (ESR)', 'سرعة الترسيب', 'ESR', 'Hematology', 'Blood', false, null],
            ['Reticulocyte Count', 'عدد الخلايا الشبكية', 'RETIC', 'Hematology', 'Blood', false, null],
            ['Peripheral Blood Smear', 'مسحة دم طرفية', 'PBS', 'Hematology', 'Blood', false, null],
            ['Hemoglobin Electrophoresis', 'الفصل الكهربائي للهيموجلوبين', 'HBEP', 'Hematology', 'Blood', false, null],
            ['Blood Group & Rh Typing', 'فصيلة الدم', 'ABO', 'Hematology', 'Blood', false, null],
            ['Sickling Test', 'اختبار الخلايا المنجلية', 'SICK', 'Hematology', 'Blood', false, null],
            ['G6PD Enzyme Activity', 'إنزيم G6PD', 'G6PD', 'Hematology', 'Blood', false, null],
            ['Direct Coombs Test', 'اختبار كومبس المباشر', 'DCT', 'Hematology', 'Blood', false, null],
            ['Indirect Coombs Test', 'اختبار كومبس غير المباشر', 'ICT', 'Hematology', 'Blood', false, null],

            // Coagulation
            ['Prothrombin Time (PT/INR)', 'زمن البروثرومبين', 'PT', 'Coagulation', 'Blood', false, null],
            ['Partial Thromboplastin Time (PTT)', 'زمن الثرومبوبلاستين الجزئي', 'PTT', 'Coagulation', 'Blood', false, null],
            ['Bleeding Time', 'زمن النزف', 'BT', 'Coagulation', 'Blood', false, null],
            ['Clotting Time', 'زمن التجلط', 'CT', 'Coagulation', 'Blood', false, null],
            ['Fibrinogen', 'الفيبرينوجين', 'FIB', 'Coagulation', 'Blood', false, null],
            ['D-Dimer', 'دي دايمر', 'DDIM', 'Coagulation', 'Blood', false, null],

            // Diabetes
            ['Fasting Blood Sugar (FBS)', 'سكر صائم', 'FBS', 'Diabetes', 'Blood', true, null],
            ['Random Blood Sugar (RBS)', 'سكر عشوائي', 'RBS', 'Diabetes', 'Blood', false, null],
            ['2-Hour Postprandial Blood Sugar', 'سكر فاطر بعد ساعتين', 'PPBS', 'Diabetes', 'Blood', false, 'Sample is taken exactly 2 hours after a regular meal.'],
            ['Glycated Hemoglobin (HbA1c)', 'السكر التراكمي', 'HBA1C', 'Diabetes', 'Blood', false, null],
            ['Oral Glucose Tolerance Test (OGTT)', 'منحنى السكر', 'OGTT', 'Diabetes', 'Blood', true, 'Fast for 8-12 hours. The test takes about 2-3 hours at the lab.'],
            ['Fasting Insulin', 'الأنسولين صائم', 'INS', 'Diabetes', 'Blood', true, null],
            ['C-Peptide', 'سي ببتيد', 'CPEP', 'Diabetes', 'Blood', true, null],
            ['HOMA-IR (Insulin Resistance)', 'مقاومة الأنسولين', 'HOMA', 'Diabetes', 'Blood', true, null],

            // Kidney function & electrolytes
            ['Blood Urea', 'البولينا في الدم', 'UREA', 'Kidney Function', 'Blood', false, null],
            ['Serum Creatinine', 'الكرياتينين', 'CREAT', 'Kidney Function', 'Blood', false, null],
            ['Blood Urea Nitrogen (BUN)', 'نيتروجين اليوريا', 'BUN', 'Kidney Function', 'Blood', false, null],
            ['Uric Acid', 'حمض اليوريك', 'URIC', 'Kidney Function', 'Blood', true, null],
            ['Cystatin C', 'سيستاتين سي', 'CYSC', 'Kidney Function', 'Blood', false, null],
            ['Sodium (Na)', 'الصوديوم', 'NA', 'Electrolytes', 'Blood', false, null],
            ['Potassium (K)', 'البوتاسيوم', 'K', 'Electrolytes', 'Blood', false, null],
            ['Chloride (Cl)', 'الكلوريد', 'CL', 'Electrolytes', 'Blood', false, null],
            ['Total Calcium', 'الكالسيوم الكلي', 'CA', 'Electrolytes', 'Blood', false, null],
            ['Ionized Calcium', 'الكالسيوم المتأين', 'ICA', 'Electrolytes', 'Blood', false, null],
            ['Phosphorus', 'الفوسفور', 'PHOS', 'Electrolytes', 'Blood', false, null],
            ['Magnesium', 'الماغنسيوم', 'MG', 'Electrolytes', 'Blood', false, null],

            // Liver function
            ['Alanine Aminotransferase (ALT/SGPT)', 'إنزيم ALT', 'ALT', 'Liver Function', 'Blood', false, null],
            ['Aspartate Aminotransferase (AST/SGOT)', 'إنزيم AST', 'AST', 'Liver Function', 'Blood', false, null],
            ['Alkaline Phosphatase (ALP)', 'الفوسفاتيز القلوي', 'ALP', 'Liver Function', 'Blood', false, null],
            ['Gamma-Glutamyl Transferase (GGT)', 'إنزيم GGT', 'GGT', 'Liver Function', 'Blood', false, null],
            ['Total Bilirubin', 'الصفراء الكلية', 'TBIL', 'Liver Function', 'Blood', false, null],
            ['Direct Bilirubin', 'الصفراء المباشرة', 'DBIL', 'Liver Function', 'Blood', false, null],
            ['Serum Albumin', 'الألبومين', 'ALB', 'Liver Function', 'Blood', false, null],
            ['Total Protein', 'البروتين الكلي', 'TP', 'Liver Function', 'Blood', false, null],
            ['Lactate Dehydrogenase (LDH)', 'إنزيم LDH', 'LDH', 'Liver Function', 'Blood', false, null],
            ['Serum Ammonia', 'الأمونيا', 'NH3', 'Liver Function', 'Blood', false, 'Sample must be delivered on ice immediately.'],

            // Lipids
            ['Lipid Profile', 'دهون الدم الكاملة', 'LIPID', 'Lipids', 'Blood', true, null],
            ['Total Cholesterol', 'الكوليسترول الكلي', 'CHOL', 'Lipids', 'Blood', true, null],
            ['Triglycerides', 'الدهون الثلاثية', 'TG', 'Lipids', 'Blood', true, null],
            ['HDL Cholesterol', 'الكوليسترول النافع', 'HDL', 'Lipids', 'Blood', true, null],
            ['LDL Cholesterol', 'الكوليسترول الضار', 'LDL', 'Lipids', 'Blood', true, null],
            ['Lipoprotein (a)', 'ليبوبروتين أ', 'LPA', 'Lipids', 'Blood', false, null],

            // Cardiac
            ['Troponin I', 'تروبونين', 'TROP', 'Cardiac', 'Blood', false, null],
            ['Creatine Kinase (CK)', 'إنزيم CK', 'CK', 'Cardiac', 'Blood', false, null],
            ['CK-MB', 'إنزيم CK-MB', 'CKMB', 'Cardiac', 'Blood', false, null],
            ['BNP', 'الببتيد المدر للصوديوم', 'BNP', 'Cardiac', 'Blood', false, null],
            ['NT-proBNP', 'NT-proBNP', 'NTBNP', 'Cardiac', 'Blood', false, null],
            ['High-Sensitivity CRP (hs-CRP)', 'بروتين سي التفاعلي عالي الحساسية', 'HSCRP', 'Cardiac', 'Blood', false, null],
            ['Homocysteine', 'الهوموسيستين', 'HCY', 'Cardiac', 'Blood', true, null],

            // Pancreas
            ['Serum Amylase', 'الأميليز', 'AMY', 'Pancreas', 'Blood', false, null],
            ['Serum Lipase', 'الليباز', 'LIP', 'Pancreas', 'Blood', false, null],

            // Thyroid
            ['Thyroid Stimulating Hormone (TSH)', 'الهرمون المنبه للغدة الدرقية', 'TSH', 'Thyroid', 'Blood', false, null],
            ['Free T3', 'هرمون T3 الحر', 'FT3', 'Thyroid', 'Blood', false, null],
            ['Free T4', 'هرمون T4 الحر', 'FT4', 'Thyroid', 'Blood', false, null],
            ['Total T3', 'هرمون T3 الكلي', 'TT3', 'Thyroid', 'Blood', false, null],
            ['Total T4', 'هرمون T4 الكلي', 'TT4', 'Thyroid', 'Blood', false, null],
            ['Anti-TPO Antibodies', 'الأجسام المضادة للغدة الدرقية TPO', 'ATPO', 'Thyroid', 'Blood', false, null],
            ['Anti-Thyroglobulin Antibodies', 'الأجسام المضادة للثيروجلوبيولين', 'ATG', 'Thyroid', 'Blood', false, null],
            ['Thyroglobulin', 'الثيروجلوبيولين', 'TG-THY', 'Thyroid', 'Blood', false, null],

            // Hormones
            ['Follicle Stimulating Hormone (FSH)', 'الهرمون المنبه للحويصلة', 'FSH', 'Hormones', 'Blood', false, 'Usually taken on day 2-3 of the menstrual cycle.'],
            ['Luteinizing Hormone (LH)', 'هرمون التبويض', 'LH', 'Hormones', 'Blood', false, 'Usually taken on day 2-3 of the menstrual cycle.'],
            ['Prolactin', 'هرمون الحليب', 'PRL', 'Hormones', 'Blood', false, 'Sample taken in the morning after 30 minutes of rest.'],
            ['Estradiol (E2)', 'الإستراديول', 'E2', 'Hormones', 'Blood', false, null],
            ['Progesterone', 'البروجستيرون', 'PROG', 'Hormones', 'Blood', false, 'Usually taken on day 21 of the menstrual cycle.'],
            ['Total Testosterone', 'التستوستيرون الكلي', 'TESTO', 'Hormones', 'Blood', false, 'Sample taken in the morning.'],
            ['Free Testosterone', 'التستوستيرون الحر', 'FTESTO', 'Hormones', 'Blood', false, 'Sample taken in the morning.'],
            ['Beta-hCG (Quantitative)', 'هرمون الحمل في الدم', 'BHCG', 'Hormones', 'Blood', false, null],
            ['Anti-Mullerian Hormone (AMH)', 'هرمون مخزون المبيض', 'AMH', 'Hormones', 'Blood', false, null],
            ['Morning Cortisol', 'الكورتيزول الصباحي', 'CORT', 'Hormones', 'Blood', false, 'Sample taken between 7 and 9 AM.'],
            ['ACTH', 'الهرمون الموجه لقشرة الكظر', 'ACTH', 'Hormones', 'Blood', false, null],
            ['Parathyroid Hormone (PTH)', 'هرمون الغدة الجار درقية', 'PTH', 'Hormones', 'Blood', false, null],
            ['Growth Hormone (GH)', 'هرمون النمو', 'GH', 'Hormones', 'Blood', true, null],
            ['DHEA-S', 'DHEA-S', 'DHEAS', 'Hormones', 'Blood', false, null],
            ['17-OH Progesterone', '17 هيدروكسي بروجستيرون', '17OHP', 'Hormones', 'Blood', false, null],

            // Vitamins, iron & minerals
            ['Vitamin D (25-OH)', 'فيتامين د', 'VITD', 'Vitamins & Minerals', 'Blood', false, null],
            ['Vitamin B12', 'فيتامين ب12', 'B12', 'Vitamins & Minerals', 'Blood', false, null],
            ['Folic Acid', 'حمض الفوليك', 'FOL', 'Vitamins & Minerals', 'Blood', false, null],
            ['Serum Iron', 'الحديد في الدم', 'FE', 'Vitamins & Minerals', 'Blood', true, null],
            ['Serum Ferritin', 'مخزون الحديد', 'FERR', 'Vitamins & Minerals', 'Blood', false, null],
            ['Total Iron Binding Capacity (TIBC)', 'السعة الكلية لربط الحديد', 'TIBC', 'Vitamins & Minerals', 'Blood', true, null],
            ['Transferrin Saturation', 'نسبة تشبع الترانسفيرين', 'TSAT', 'Vitamins & Minerals', 'Blood', true, null],
            ['Serum Zinc', 'الزنك', 'ZN', 'Vitamins & Minerals', 'Blood', false, null],
            ['Serum Copper', 'النحاس', 'CU', 'Vitamins & Minerals', 'Blood', false, null],

            // Immunology & inflammation
            ['C-Reactive Protein (CRP)', 'بروتين سي التفاعلي', 'CRP', 'Immunology', 'Blood', false, null],
            ['Antistreptolysin O (ASO)', 'أجسام مضادة للستربتوليسين', 'ASO', 'Immunology', 'Blood', false, null],
            ['Rheumatoid Factor (RF)', 'معامل الروماتويد', 'RF', 'Immunology', 'Blood', false, null],
            ['Anti-CCP Antibodies', 'الأجسام المضادة Anti-CCP', 'ACCP', 'Immunology', 'Blood', false, null],
            ['Antinuclear Antibodies (ANA)', 'الأجسام المضادة للنواة', 'ANA', 'Immunology', 'Blood', false, null],
            ['Anti-dsDNA', 'الأجسام المضادة للحمض النووي', 'DSDNA', 'Immunology', 'Blood', false, null],
            ['Complement C3', 'المكمل C3', 'C3', 'Immunology', 'Blood', false, null],
            ['Complement C4', 'المكمل C4', 'C4', 'Immunology', 'Blood', false, null],
            ['Total IgE', 'الأجسام المضادة IgE', 'IGE', 'Immunology', 'Blood', false, null],
            ['Procalcitonin', 'البروكالسيتونين', 'PCT', 'Immunology', 'Blood', false, null],
            ['HLA-B27', 'HLA-B27', 'HLAB27', 'Immunology', 'Blood', false, null],

            // Infectious serology
            ['Hepatitis B Surface Antigen (HBsAg)', 'فيروس بي HBsAg', 'HBSAG', 'Serology', 'Blood', false, null],
            ['Hepatitis B Surface Antibody (Anti-HBs)', 'مناعة فيروس بي', 'AHBS', 'Serology', 'Blood', false, null],
            ['Hepatitis B Core Antibody (Anti-HBc)', 'الأجسام المضادة لفيروس بي Anti-HBc', 'AHBC', 'Serology', 'Blood', false, null],
            ['HBV DNA PCR (Quantitative)', 'تحليل PCR لفيروس بي', 'HBVPCR', 'Serology', 'Blood', false, null],
            ['Hepatitis C Antibody (HCV Ab)', 'فيروس سي', 'HCVAB', 'Serology', 'Blood', false, null],
            ['HCV RNA PCR (Quantitative)', 'تحليل PCR لفيروس سي', 'HCVPCR', 'Serology', 'Blood', false, null],
            ['HIV 1 & 2 Antibodies', 'فيروس نقص المناعة', 'HIV', 'Serology', 'Blood', false, null],
            ['Widal Test', 'تحليل التيفود', 'WIDAL', 'Serology', 'Blood', false, null],
            ['Brucella Antibodies', 'الحمى المالطية', 'BRUC', 'Serology', 'Blood', false, null],
            ['H. Pylori Antibodies', 'جرثومة المعدة في الدم', 'HPAB', 'Serology', 'Blood', false, null],
            ['TORCH Panel', 'تحاليل TORCH', 'TORCH', 'Serology', 'Blood', false, null],
            ['Toxoplasma IgG & IgM', 'التوكسوبلازما', 'TOXO', 'Serology', 'Blood', false, null],
            ['Rubella IgG', 'الحصبة الألمانية', 'RUB', 'Serology', 'Blood', false, null],
            ['Cytomegalovirus (CMV) IgM', 'الفيروس المضخم للخلايا', 'CMV', 'Serology', 'Blood', false, null],
            ['Epstein-Barr Virus (EBV) VCA IgM', 'فيروس إبشتاين بار', 'EBV', 'Serology', 'Blood', false, null],
            ['VDRL / RPR', 'تحليل الزهري', 'VDRL', 'Serology', 'Blood', false, null],
            ['Dengue NS1 Antigen', 'حمى الضنك', 'DENGUE', 'Serology', 'Blood', false, null],
            ['Malaria Blood Film', 'فحص الملاريا', 'MAL', 'Serology', 'Blood', false, null],
            ['COVID-19 PCR', 'مسحة كورونا PCR', 'COVPCR', 'Serology', 'Swab', false, null],
            ['Influenza A & B Rapid Test', 'الإنفلونزا أ و ب', 'FLU', 'Serology', 'Swab', false, null],

            // Tumor markers
            ['Total PSA', 'دلالات البروستاتا الكلية', 'PSA', 'Tumor Markers', 'Blood', false, 'Avoid ejaculation and cycling for 48 hours before the test.'],
            ['Free PSA', 'دلالات البروستاتا الحرة', 'FPSA', 'Tumor Markers', 'Blood', false, null],
            ['CEA', 'دلالات القولون CEA', 'CEA', 'Tumor Markers', 'Blood', false, null],
            ['CA 125', 'دلالات المبيض CA 125', 'CA125', 'Tumor Markers', 'Blood', false, null],
            ['CA 19-9', 'دلالات البنكرياس CA 19-9', 'CA199', 'Tumor Markers', 'Blood', false, null],
            ['CA 15-3', 'دلالات الثدي CA 15-3', 'CA153', 'Tumor Markers', 'Blood', false, null],
            ['Alpha-Fetoprotein (AFP)', 'دلالات الكبد AFP', 'AFP', 'Tumor Markers', 'Blood', false, null],

            // Urine
            ['Complete Urine Analysis', 'تحليل بول كامل', 'UA', 'Urine', 'Urine', false, 'First morning midstream sample in a sterile cup.'],
            ['Urine Culture & Sensitivity', 'مزرعة بول', 'UCS', 'Urine', 'Urine', false, 'Midstream sample in a sterile cup, before starting antibiotics.'],
            ['24-Hour Urine Protein', 'بروتين البول في 24 ساعة', 'U24P', 'Urine', 'Urine', false, 'Collect all urine for 24 hours in the container provided by the lab.'],
            ['Urine Albumin/Creatinine Ratio', 'نسبة الألبومين للكرياتينين في البول', 'UACR', 'Urine', 'Urine', false, null],
            ['Urine Pregnancy Test', 'اختبار الحمل في البول', 'UPT', 'Urine', 'Urine', false, 'First morning urine sample.'],
            ['Creatinine Clearance', 'معدل تصفية الكرياتينين', 'CRCL', 'Urine', 'Urine', false, 'Requires a 24-hour urine collection and a blood sample.'],

            // Stool
            ['Complete Stool Analysis', 'تحليل براز كامل', 'SA', 'Stool', 'Stool', false, null],
            ['Stool Occult Blood', 'دم خفي في البراز', 'FOB', 'Stool', 'Stool', false, null],
            ['Stool Culture', 'مزرعة براز', 'SCULT', 'Stool', 'Stool', false, null],
            ['H. Pylori Stool Antigen', 'جرثومة المعدة في البراز', 'HPSAG', 'Stool', 'Stool', false, 'Stop proton pump inhibitors 2 weeks and antibiotics 4 weeks before the test.'],
            ['Fecal Calprotectin', 'الكالبروتكتين في البراز', 'CALP', 'Stool', 'Stool', false, null],

            // Microbiology
            ['Blood Culture', 'مزرعة دم', 'BCULT', 'Microbiology', 'Blood', false, 'Taken before starting antibiotics, ideally during fever.'],
            ['Throat Swab Culture', 'مزرعة مسحة الحلق', 'TSC', 'Microbiology', 'Swab', false, null],
            ['Sputum Culture & Sensitivity', 'مزرعة بلغم', 'SPC', 'Microbiology', 'Sputum', false, 'Early morning deep cough sample before brushing teeth.'],
            ['Sputum for Acid-Fast Bacilli (AFB)', 'بلغم للدرن', 'AFB', 'Microbiology', 'Sputum', false, 'Three early morning samples on three consecutive days.'],
            ['Wound Swab Culture', 'مزرعة جرح', 'WSC', 'Microbiology', 'Swab', false, null],
            ['High Vaginal Swab Culture', 'مزرعة مسحة مهبلية', 'HVS', 'Microbiology', 'Swab', false, null],

            // Other specimens
            ['Semen Analysis', 'تحليل السائل المنوي', 'SEMEN', 'Andrology', 'Semen', false, 'Abstain for 3-5 days. Deliver the sample to the lab within 1 hour.'],
            ['CSF Analysis', 'تحليل السائل النخاعي', 'CSF', 'Neurology', 'CSF', false, null],
            ['Histopathology (Biopsy)', 'فحص عينة نسيجية', 'HISTO', 'Pathology', 'Tissue', false, null],
            ['Pap Smear', 'مسحة عنق الرحم', 'PAP', 'Pathology', 'Swab', false, 'Avoid intercourse and vaginal products for 48 hours. Not during menstruation.'],
        ];
    }
}
