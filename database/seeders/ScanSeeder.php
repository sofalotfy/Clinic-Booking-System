<?php

namespace Database\Seeders;

use App\Models\Scan;
use Illuminate\Database\Seeder;

class ScanSeeder extends Seeder
{
    private const CONTRAST = 'Fast for 4-6 hours. Bring a recent serum creatinine result. Tell the staff about any allergy, diabetes medication (metformin) or pregnancy.';

    private const FULL_BLADDER = 'Drink 1 liter of water 1 hour before the scan and do not empty your bladder.';

    private const ABDOMEN_FAST = 'Fast for 6-8 hours before the scan.';

    private const CODE_PREFIX = [
        'X-Ray' => 'XR',
        'CT' => 'CT',
        'MRI' => 'MR',
        'Ultrasound' => 'US',
        'Doppler' => 'DP',
        'Mammography' => 'MG',
        'Fluoroscopy' => 'FL',
        'DEXA' => 'DX',
        'PET' => 'PT',
        'Nuclear Medicine' => 'NM',
    ];

    /**
     * Common imaging scans, keyed by name so re-running only updates them.
     * Codes are generated per modality, e.g. CT-003.
     *
     * [name, arabic name, modality, body part, with contrast, preparation]
     */
    public function run(): void
    {
        $counters = [];

        foreach ($this->scans() as [$name, $nameAr, $modality, $bodyPart, $contrast, $preparation]) {
            $prefix = self::CODE_PREFIX[$modality];
            $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;

            Scan::updateOrCreate(['name' => $name], [
                'name_ar' => $nameAr,
                'code' => sprintf('%s-%03d', $prefix, $counters[$prefix]),
                'modality' => $modality,
                'body_part' => $bodyPart,
                'with_contrast' => $contrast,
                'preparation' => $preparation ?? ($contrast ? self::CONTRAST : null),
                'is_active' => true,
            ]);
        }
    }

    private function scans(): array
    {
        return [
            // X-Ray
            ['Chest X-Ray (PA View)', 'أشعة عادية على الصدر', 'X-Ray', 'Chest', false, null],
            ['Chest X-Ray (PA & Lateral)', 'أشعة على الصدر وضع أمامي وجانبي', 'X-Ray', 'Chest', false, null],
            ['Plain Abdomen X-Ray (Erect)', 'أشعة عادية على البطن واقف', 'X-Ray', 'Abdomen', false, null],
            ['KUB X-Ray', 'أشعة على الكلى والحالب والمثانة', 'X-Ray', 'Abdomen', false, null],
            ['Skull X-Ray', 'أشعة على الجمجمة', 'X-Ray', 'Head', false, null],
            ['Paranasal Sinuses X-Ray', 'أشعة على الجيوب الأنفية', 'X-Ray', 'Sinuses', false, null],
            ['Cervical Spine X-Ray', 'أشعة على الفقرات العنقية', 'X-Ray', 'Cervical Spine', false, null],
            ['Thoracic Spine X-Ray', 'أشعة على الفقرات الصدرية', 'X-Ray', 'Thoracic Spine', false, null],
            ['Lumbosacral Spine X-Ray', 'أشعة على الفقرات القطنية', 'X-Ray', 'Lumbar Spine', false, null],
            ['Pelvis X-Ray', 'أشعة على الحوض', 'X-Ray', 'Pelvis', false, null],
            ['Shoulder X-Ray', 'أشعة على الكتف', 'X-Ray', 'Shoulder', false, null],
            ['Elbow X-Ray', 'أشعة على الكوع', 'X-Ray', 'Elbow', false, null],
            ['Wrist X-Ray', 'أشعة على الرسغ', 'X-Ray', 'Wrist', false, null],
            ['Hand X-Ray', 'أشعة على اليد', 'X-Ray', 'Hand', false, null],
            ['Hip X-Ray', 'أشعة على مفصل الفخذ', 'X-Ray', 'Hip', false, null],
            ['Knee X-Ray', 'أشعة على الركبة', 'X-Ray', 'Knee', false, null],
            ['Ankle X-Ray', 'أشعة على الكاحل', 'X-Ray', 'Ankle', false, null],
            ['Foot X-Ray', 'أشعة على القدم', 'X-Ray', 'Foot', false, null],
            ['Femur X-Ray', 'أشعة على عظمة الفخذ', 'X-Ray', 'Thigh', false, null],
            ['Tibia & Fibula X-Ray', 'أشعة على الساق', 'X-Ray', 'Leg', false, null],
            ['Humerus X-Ray', 'أشعة على عظمة العضد', 'X-Ray', 'Arm', false, null],
            ['Forearm X-Ray', 'أشعة على الساعد', 'X-Ray', 'Forearm', false, null],
            ['Bone Age X-Ray', 'أشعة عمر العظام', 'X-Ray', 'Hand', false, null],
            ['Dental Panoramic X-Ray (OPG)', 'أشعة بانوراما على الأسنان', 'X-Ray', 'Teeth', false, null],
            ['Intravenous Urography (IVU)', 'أشعة بالصبغة على المسالك البولية', 'X-Ray', 'Urinary Tract', true, null],

            // Fluoroscopy
            ['Barium Swallow', 'أشعة بالباريوم على المريء', 'Fluoroscopy', 'Esophagus', false, 'Fast for 6 hours before the study.'],
            ['Barium Meal', 'أشعة بالباريوم على المعدة', 'Fluoroscopy', 'Stomach', false, 'Fast for 8 hours before the study.'],
            ['Barium Enema', 'حقنة شرجية بالباريوم', 'Fluoroscopy', 'Colon', false, 'Bowel preparation with laxatives the day before, as instructed by the radiology center.'],
            ['Hysterosalpingography (HSG)', 'أشعة بالصبغة على الرحم والأنابيب', 'Fluoroscopy', 'Uterus & Tubes', true, 'Done on day 7-10 of the cycle, after menstruation ends and before ovulation.'],
            ['Micturating Cystourethrogram (MCUG)', 'أشعة بالصبغة على المثانة أثناء التبول', 'Fluoroscopy', 'Bladder', true, 'A urine culture should be negative before the study.'],

            // CT
            ['CT Brain (Non-Contrast)', 'أشعة مقطعية على المخ بدون صبغة', 'CT', 'Brain', false, null],
            ['CT Brain with Contrast', 'أشعة مقطعية على المخ بالصبغة', 'CT', 'Brain', true, null],
            ['High-Resolution CT Chest (HRCT)', 'أشعة مقطعية عالية الدقة على الصدر', 'CT', 'Chest', false, null],
            ['CT Chest with Contrast', 'أشعة مقطعية على الصدر بالصبغة', 'CT', 'Chest', true, null],
            ['Low-Dose CT Lung Screening', 'أشعة مقطعية منخفضة الجرعة للرئة', 'CT', 'Chest', false, null],
            ['CT Abdomen & Pelvis with Contrast', 'أشعة مقطعية على البطن والحوض بالصبغة', 'CT', 'Abdomen & Pelvis', true, null],
            ['CT KUB (Non-Contrast)', 'أشعة مقطعية على المسالك البولية بدون صبغة', 'CT', 'Urinary Tract', false, null],
            ['CT Paranasal Sinuses', 'أشعة مقطعية على الجيوب الأنفية', 'CT', 'Sinuses', false, null],
            ['CT Cervical Spine', 'أشعة مقطعية على الفقرات العنقية', 'CT', 'Cervical Spine', false, null],
            ['CT Lumbar Spine', 'أشعة مقطعية على الفقرات القطنية', 'CT', 'Lumbar Spine', false, null],
            ['CT Coronary Angiography', 'أشعة مقطعية على الشرايين التاجية', 'CT', 'Heart', true, 'Avoid caffeine for 12 hours. Fast for 4 hours. Bring a recent creatinine result.'],
            ['CT Pulmonary Angiography', 'أشعة مقطعية على شرايين الرئة', 'CT', 'Chest', true, null],
            ['CT Angiography Brain', 'أشعة مقطعية على شرايين المخ', 'CT', 'Brain', true, null],
            ['CT Temporal Bones', 'أشعة مقطعية على عظام الأذن', 'CT', 'Ear', false, null],
            ['CT Orbits', 'أشعة مقطعية على محجر العين', 'CT', 'Orbits', false, null],
            ['CT Neck with Contrast', 'أشعة مقطعية على الرقبة بالصبغة', 'CT', 'Neck', true, null],
            ['CT Virtual Colonoscopy', 'منظار قولون افتراضي بالأشعة المقطعية', 'CT', 'Colon', false, 'Bowel preparation with laxatives the day before, as instructed by the radiology center.'],

            // MRI
            ['MRI Brain (Non-Contrast)', 'رنين مغناطيسي على المخ', 'MRI', 'Brain', false, null],
            ['MRI Brain with Contrast', 'رنين مغناطيسي على المخ بالصبغة', 'MRI', 'Brain', true, null],
            ['MRI Cervical Spine', 'رنين مغناطيسي على الفقرات العنقية', 'MRI', 'Cervical Spine', false, null],
            ['MRI Dorsal Spine', 'رنين مغناطيسي على الفقرات الصدرية', 'MRI', 'Thoracic Spine', false, null],
            ['MRI Lumbosacral Spine', 'رنين مغناطيسي على الفقرات القطنية', 'MRI', 'Lumbar Spine', false, null],
            ['MRI Knee', 'رنين مغناطيسي على الركبة', 'MRI', 'Knee', false, null],
            ['MRI Shoulder', 'رنين مغناطيسي على الكتف', 'MRI', 'Shoulder', false, null],
            ['MRI Hip', 'رنين مغناطيسي على مفصل الفخذ', 'MRI', 'Hip', false, null],
            ['MRI Ankle', 'رنين مغناطيسي على الكاحل', 'MRI', 'Ankle', false, null],
            ['MRI Wrist', 'رنين مغناطيسي على الرسغ', 'MRI', 'Wrist', false, null],
            ['MRI Pelvis with Contrast', 'رنين مغناطيسي على الحوض بالصبغة', 'MRI', 'Pelvis', true, null],
            ['Multiparametric MRI Prostate', 'رنين مغناطيسي متعدد المعايير على البروستاتا', 'MRI', 'Prostate', true, null],
            ['MRI Breast with Contrast', 'رنين مغناطيسي على الثدي بالصبغة', 'MRI', 'Breast', true, 'Best done on day 7-14 of the menstrual cycle.'],
            ['MRCP', 'رنين مغناطيسي على القنوات المرارية', 'MRI', 'Biliary Tract', false, 'Fast for 4-6 hours before the scan.'],
            ['MRI Abdomen with Contrast', 'رنين مغناطيسي على البطن بالصبغة', 'MRI', 'Abdomen', true, null],
            ['Cardiac MRI with Contrast', 'رنين مغناطيسي على القلب بالصبغة', 'MRI', 'Heart', true, null],
            ['MR Angiography Brain', 'رنين مغناطيسي على شرايين المخ', 'MRI', 'Brain', false, null],
            ['MRI Pituitary with Contrast', 'رنين مغناطيسي على الغدة النخامية بالصبغة', 'MRI', 'Pituitary', true, null],
            ['MRI Orbits', 'رنين مغناطيسي على محجر العين', 'MRI', 'Orbits', false, null],
            ['MRI Temporomandibular Joints (TMJ)', 'رنين مغناطيسي على مفصل الفك', 'MRI', 'Jaw', false, null],
            ['Fetal MRI', 'رنين مغناطيسي على الجنين', 'MRI', 'Fetus', false, null],

            // Ultrasound
            ['Ultrasound Abdomen', 'سونار على البطن', 'Ultrasound', 'Abdomen', false, self::ABDOMEN_FAST],
            ['Ultrasound Pelvis (Transabdominal)', 'سونار على الحوض', 'Ultrasound', 'Pelvis', false, self::FULL_BLADDER],
            ['Ultrasound Abdomen & Pelvis', 'سونار على البطن والحوض', 'Ultrasound', 'Abdomen & Pelvis', false, self::ABDOMEN_FAST.' '.self::FULL_BLADDER],
            ['Transvaginal Ultrasound', 'سونار مهبلي', 'Ultrasound', 'Pelvis', false, 'Empty your bladder before the scan.'],
            ['Obstetric Ultrasound (1st Trimester)', 'سونار حمل الثلث الأول', 'Ultrasound', 'Fetus', false, null],
            ['Fetal Anomaly Scan', 'سونار رباعي الأبعاد للتشوهات', 'Ultrasound', 'Fetus', false, 'Best done between weeks 18 and 22 of pregnancy.'],
            ['Obstetric Growth Scan', 'سونار متابعة نمو الجنين', 'Ultrasound', 'Fetus', false, null],
            ['Folliculometry', 'متابعة التبويض بالسونار', 'Ultrasound', 'Ovaries', false, 'Usually starts on day 9-10 of the cycle and is repeated as instructed.'],
            ['Ultrasound Thyroid & Neck', 'سونار على الغدة الدرقية والرقبة', 'Ultrasound', 'Neck', false, null],
            ['Ultrasound Breast', 'سونار على الثدي', 'Ultrasound', 'Breast', false, null],
            ['Ultrasound Scrotum', 'سونار على الخصية', 'Ultrasound', 'Scrotum', false, null],
            ['Ultrasound Kidneys & Bladder', 'سونار على الكلى والمثانة', 'Ultrasound', 'Urinary Tract', false, self::FULL_BLADDER],
            ['Transrectal Ultrasound Prostate (TRUS)', 'سونار شرجي على البروستاتا', 'Ultrasound', 'Prostate', false, null],
            ['Ultrasound Soft Tissue', 'سونار على الأنسجة الرخوة', 'Ultrasound', 'Soft Tissue', false, null],
            ['Musculoskeletal Ultrasound Shoulder', 'سونار على الكتف', 'Ultrasound', 'Shoulder', false, null],
            ['Neonatal Brain Ultrasound', 'سونار على مخ حديثي الولادة', 'Ultrasound', 'Brain', false, null],
            ['Infant Hip Ultrasound', 'سونار على مفصل الفخذ للرضع', 'Ultrasound', 'Hip', false, null],
            ['Echocardiography', 'إيكو على القلب', 'Ultrasound', 'Heart', false, null],

            // Doppler
            ['Carotid Doppler', 'دوبلر على الشرايين السباتية', 'Doppler', 'Neck', false, null],
            ['Lower Limb Venous Doppler', 'دوبلر على أوردة الساقين', 'Doppler', 'Lower Limbs', false, null],
            ['Lower Limb Arterial Doppler', 'دوبلر على شرايين الساقين', 'Doppler', 'Lower Limbs', false, null],
            ['Upper Limb Venous Doppler', 'دوبلر على أوردة الذراعين', 'Doppler', 'Upper Limbs', false, null],
            ['Renal Artery Doppler', 'دوبلر على شرايين الكلى', 'Doppler', 'Kidneys', false, self::ABDOMEN_FAST],
            ['Obstetric Doppler', 'دوبلر على الحمل', 'Doppler', 'Fetus', false, null],
            ['Scrotal Doppler', 'دوبلر على الخصية', 'Doppler', 'Scrotum', false, null],
            ['Portal Venous Doppler', 'دوبلر على الوريد البابي', 'Doppler', 'Liver', false, self::ABDOMEN_FAST],

            // Mammography
            ['Screening Mammography (Bilateral)', 'ماموجرام للكشف المبكر', 'Mammography', 'Breast', false, 'Do not use deodorant or powder on the day of the exam. Best done on day 7-10 of the cycle.'],
            ['Diagnostic Mammography', 'ماموجرام تشخيصي', 'Mammography', 'Breast', false, 'Do not use deodorant or powder on the day of the exam.'],
            ['3D Mammography (Tomosynthesis)', 'ماموجرام ثلاثي الأبعاد', 'Mammography', 'Breast', false, 'Do not use deodorant or powder on the day of the exam.'],

            // DEXA
            ['DEXA Bone Density (Spine & Hip)', 'قياس كثافة العظام', 'DEXA', 'Spine & Hip', false, 'Stop calcium supplements 24 hours before the scan.'],
            ['DEXA Whole Body Composition', 'قياس مكونات الجسم', 'DEXA', 'Whole Body', false, null],

            // PET
            ['PET-CT Whole Body', 'مسح ذري PET-CT على الجسم كله', 'PET', 'Whole Body', true, 'Fast for 6 hours. Avoid strenuous exercise for 24 hours. Diabetic patients need special instructions.'],
            ['PET-CT Brain', 'مسح ذري PET-CT على المخ', 'PET', 'Brain', false, 'Fast for 6 hours before the scan.'],

            // Nuclear medicine
            ['Whole Body Bone Scan', 'مسح ذري على العظام', 'Nuclear Medicine', 'Whole Body', false, 'Drink plenty of water after the injection. Imaging is done 3 hours later.'],
            ['Thyroid Scan', 'مسح ذري على الغدة الدرقية', 'Nuclear Medicine', 'Thyroid', false, 'Stop thyroid medications and avoid iodine-rich food as instructed.'],
            ['Renal DMSA Scan', 'مسح ذري على الكلى DMSA', 'Nuclear Medicine', 'Kidneys', false, null],
            ['Renal DTPA Scan', 'مسح ذري على وظائف الكلى DTPA', 'Nuclear Medicine', 'Kidneys', false, 'Drink 2-3 glasses of water before the scan.'],
            ['Myocardial Perfusion Scan', 'مسح ذري على عضلة القلب', 'Nuclear Medicine', 'Heart', false, 'Avoid caffeine for 24 hours. Fast for 4 hours before the scan.'],
            ['HIDA Scan', 'مسح ذري على المرارة', 'Nuclear Medicine', 'Gallbladder', false, 'Fast for 4-6 hours before the scan.'],
            ['Parathyroid Sestamibi Scan', 'مسح ذري على الغدد الجار درقية', 'Nuclear Medicine', 'Parathyroid', false, null],
        ];
    }
}
