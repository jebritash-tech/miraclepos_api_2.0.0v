<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ═══════════════════════════════════════════════════════════════════
 * MedicinesSeeder — كتالوج الأدوية الكامل
 * ═══════════════════════════════════════════════════════════════════
 *
 * المصدر: Medicines.xlsx
 *
 * المبدأ:
 *   ✅ يعتمد على الوحدات المُنشأة مسبقاً في CoreDataSeeder
 *   ✅ لا يُنشئ وحدات جديدة في جدول units (إلا إن كانت ناقصة)
 *   ✅ يستخدم معامل افتراضي = 1 لكل الوحدات
 *   ✅ idempotent (يمكن تشغيله أكثر من مرة بأمان)
 *
 * التشغيل:
 *   php artisan db:seed --class=MedicinesSeeder
 *
 * أو تلقائياً بعد CoreDataSeeder عبر DatabaseSeeder.
 * ═══════════════════════════════════════════════════════════════════
 */
class MedicinesSeeder extends Seeder
{
    /**
     * خريطة تحويل رموز Excel إلى رموز جدول units
     *
     * الوحدات المركبة (box-strip) تُفكَّك إلى وحدتين منفصلتين.
     */
    private const UNIT_MAP = [
        // رموز مباشرة
        'box'    => 'BOX',
        'strip'  => 'STRIP',
        'pic'    => 'PIC',
        'piece'  => 'PIECE',
        'pc'     => 'PIC',
        'tab'    => 'TAB',
        'cap'    => 'CAP',
        'bottle' => 'BOTTLE',
        'bot'    => 'BOTTLE',
        'tube'   => 'TUBE',
        'tub'    => 'TUB',
        'vial'   => 'VIAL',
        'amp'    => 'AMP',
        'ampul'  => 'AMP',
        'spray'  => 'SPRAY',
        'sachet' => 'SACHET',
        'ml'     => 'ML',
        'g'      => 'G',
        'drop'   => 'DROP',
        'inh'    => 'INH',
        'drip'   => 'DRIP',
        'set'    => 'SET',
        'supp'   => 'SUPP',
    ];

    public function run(): void
    {
        $this->command->info('🚀 بدء MedicinesSeeder...');

        // التأكد من وجود الوحدات الأساسية
        $units = $this->loadUnits();
        if (empty($units)) {
            $this->command->error('❌ لا توجد وحدات في جدول units. شغّل CoreDataSeeder أولاً.');
            return;
        }

        $created = 0;
        $updated = 0;
        $unitsCreated = 0;

        DB::transaction(function () use ($units, &$created, &$updated, &$unitsCreated) {
            foreach ($this->getMedicines() as $medicine) {
                $name = trim($medicine['name'] ?? '');
                if ($name === '') continue;

                // ابحث عن الدواء أو أنشئه
                $medicineId = DB::table('medicines')->where('name', $name)->value('id');

                if (!$medicineId) {
                    $medicineId = DB::table('medicines')->insertGetId([
                        'name'             => $name,
                        'scientific_name'  => null,
                        'notes'            => null,
                        'pricing_method'   => 'local',
                        'pricing_rule_id'  => null,
                        'category_id'      => null,
                        'strips_per_box'   => 1,
                        'pieces_per_strip' => 1,
                        'allow_box_sale'   => 1,
                        'allow_strip_sale' => 1,
                        'allow_piece_sale' => 1,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $created++;
                } else {
                    DB::table('medicines')
                        ->where('id', $medicineId)
                        ->update(['updated_at' => now()]);
                    $updated++;
                }

                // اربط الوحدات بالدواء
                $unitsCreated += $this->attachUnits(
                    $medicineId,
                    $medicine['unit'] ?? null,
                    $units
                );
            }
        });

        $this->command->line("   ✅ {$created} دواء جديد، {$updated} محدّث");
        $this->command->line("   ✅ {$unitsCreated} ربط وحدة/دواء");
        $this->command->info('✅ اكتمل MedicinesSeeder.');
    }

    /**
     * تحميل الوحدات من قاعدة البيانات
     */
    private function loadUnits(): array
    {
        return DB::table('units')
            ->where('active', 1)
            ->pluck('id', 'symbol')
            ->toArray();
    }

    /**
     * ربط الوحدات بدواء معين
     *
     * @return int عدد الروابط المُنشأة
     */
    private function attachUnits(int $medicineId, ?string $definition, array $units): int
    {
        if (!$definition) return 0;

        $definition = strtolower(trim($definition));
        if ($definition === '') return 0;

        $parts = explode('-', $definition);
        $created = 0;
        $sortOrder = 1;

        foreach ($parts as $index => $part) {
            $part = trim($part);
            if ($part === '') continue;

            $symbol = self::UNIT_MAP[$part] ?? null;
            if (!$symbol || !isset($units[$symbol])) continue;

            $unitId = $units[$symbol];
            $isBase = ($index === count($parts) - 1) ? 1 : 0;

            // تجنّب التكرار
            $exists = DB::table('medicine_units')
                ->where('medicine_id', $medicineId)
                ->where('unit_id', $unitId)
                ->exists();

            if ($exists) {
                $sortOrder++;
                continue;
            }

            DB::table('medicine_units')->insert([
                'medicine_id' => $medicineId,
                'unit_id'     => $unitId,
                'factor'      => 1,
                'barcode'     => null,
                'is_base'     => $isBase,
                'sort_order'  => $sortOrder,
                'allow_sale'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $created++;
            $sortOrder++;
        }

        return $created;
    }

    /**
     * قائمة الأدوية الكاملة من ملف Medicines.xlsx
     */
    private function getMedicines(): array
    {
        return [
            // ═══════════ الجزء 1 ═══════════
            ['name' => 'Conther 80/480', 'unit' => 'box-strip'],
            ['name' => 'Lumiart 80/480', 'unit' => 'box-strip'],
            ['name' => 'Artemether 20/120', 'unit' => 'box'],
            ['name' => 'Amidpine 5mg', 'unit' => 'box-strip'],
            ['name' => 'HCQ 200', 'unit' => 'box-strip'],
            ['name' => 'Prima Pro', 'unit' => 'box-strip'],
            ['name' => 'Hero Lac 1', 'unit' => 'box-strip'],
            ['name' => 'Pretty Lac LF', 'unit' => 'box'],
            ['name' => 'Stevia', 'unit' => 'box-strip'],
            ['name' => 'Doxim 200', 'unit' => 'box'],
            ['name' => 'Bactofix 400', 'unit' => 'box-strip'],
            ['name' => 'Amixime 400', 'unit' => 'box-strip'],
            ['name' => 'Amixime 200', 'unit' => 'box'],
            ['name' => 'Nilozol 250', 'unit' => 'box-strip'],
            ['name' => 'Syrine 3ml', 'unit' => 'pic'],
            ['name' => 'Edizone 500', 'unit' => 'vial'],
            ['name' => 'Ceftriol 1000mg', 'unit' => 'vial'],
            ['name' => 'Vision-Aid', 'unit' => 'box'],
            ['name' => 'Vitan 3', 'unit' => 'box-strip'],
            ['name' => 'Mecovil 12', 'unit' => 'box'],
            ['name' => 'Nervon 500', 'unit' => 'box'],
            ['name' => 'Mecoba 500', 'unit' => 'box-strip'],
            ['name' => 'Vitamin B12 1000', 'unit' => 'box'],
            ['name' => 'Neurovox', 'unit' => 'box-strip'],
            ['name' => 'Omevox', 'unit' => 'box-strip'],
            ['name' => 'Carnitine Forte', 'unit' => 'box-strip'],
            ['name' => 'Nutramax', 'unit' => 'box-strip'],
            ['name' => 'Nutramax Gold', 'unit' => 'box-strip'],
            ['name' => 'Diavox', 'unit' => 'box-strip'],
            ['name' => 'Fertilex Men', 'unit' => 'box-strip'],
            ['name' => 'Lumiart 20/120', 'unit' => 'box-strip'],
            ['name' => 'Daflon 500', 'unit' => 'box-strip'],
            ['name' => 'Cod Liver Oil', 'unit' => 'box-strip'],
            ['name' => 'HG 9 Sachet', 'unit' => 'box-strip'],
            ['name' => 'Fertilex Women', 'unit' => 'box-strip'],
            ['name' => 'Amiron +F', 'unit' => 'box-strip'],
            ['name' => 'Ferolin', 'unit' => 'box-strip'],
            ['name' => 'Haemovox', 'unit' => 'box-strip'],
            ['name' => 'Ferron Forte', 'unit' => 'box-strip'],
            ['name' => 'Fe-Full', 'unit' => 'box-strip'],
            ['name' => 'Vitaferol', 'unit' => 'box-strip'],
            ['name' => 'Bonvox 500', 'unit' => 'box-strip'],
            ['name' => 'Cali Pure', 'unit' => 'box-strip'],
            ['name' => 'Calim', 'unit' => 'box-strip'],
            ['name' => 'Enzyrex', 'unit' => 'box-strip'],
            ['name' => 'SB-First', 'unit' => 'box-strip'],
            ['name' => 'Bisadyl', 'unit' => 'box-strip'],
            ['name' => 'Pregnavox', 'unit' => 'box-strip'],
            ['name' => 'Joemega 3', 'unit' => 'box-strip'],
            ['name' => 'Omega Best', 'unit' => 'box-strip'],
            ['name' => 'Liver Heal', 'unit' => 'box-strip'],
            ['name' => 'HSN', 'unit' => 'box-strip-tab'],
            ['name' => 'Fenugreek', 'unit' => 'box-strip'],
            ['name' => 'Norethisterone', 'unit' => 'box-strip'],
            ['name' => 'Norcutin', 'unit' => null],
            ['name' => 'Norazor', 'unit' => 'box-strip'],
            ['name' => 'CD Solicin 10', 'unit' => 'box-strip'],
            ['name' => 'Virustat 200', 'unit' => 'box-strip'],
            ['name' => 'Virustat 400', 'unit' => 'box-strip'],
            ['name' => 'Injidime 1g', 'unit' => 'vial'],
            ['name' => 'Trizid 1000', 'unit' => 'vial'],
            ['name' => 'Injroxime 750', 'unit' => 'vial'],
            ['name' => 'Xiotil 750', 'unit' => 'vial'],
            ['name' => 'Funtum 1.5g', 'unit' => 'vial'],
            ['name' => 'Meroscot 1g', 'unit' => 'vial'],
            ['name' => 'Monan 1g', 'unit' => 'vial'],
            ['name' => 'Edispoin 100', 'unit' => 'box-strip'],
            ['name' => 'Aspruna 100', 'unit' => 'box-strip'],
            ['name' => 'Faderin 100', 'unit' => 'box-strip'],
            ['name' => 'Aspruna 75', 'unit' => 'box-strip'],
            ['name' => 'Edisprin 75', 'unit' => 'box-strip'],
            ['name' => 'Aspicor 75', 'unit' => 'box-strip'],
            ['name' => 'Forsitor 20', 'unit' => 'box-strip'],
            ['name' => 'Cholerose 10mg', 'unit' => 'box-strip'],
            ['name' => 'Amistatin 40', 'unit' => 'box-strip'],
            ['name' => 'Atornova 20', 'unit' => 'box-strip'],
            ['name' => 'Biscot', 'unit' => 'box-strip'],
            ['name' => 'Amicor 5mg', 'unit' => 'box-strip'],
            ['name' => 'Amicor 2.5mg', 'unit' => 'box-strip'],
            ['name' => 'Amidipin 10mg', 'unit' => 'box-strip'],
            ['name' => 'CD Amlovan 10/160', 'unit' => 'box-strip'],
            ['name' => 'CD Amlovan 5/160', 'unit' => 'box-strip'],
            ['name' => 'CD Amlovan 5/180', 'unit' => 'box-strip'],
            ['name' => 'Angiosar Plus 160/5', 'unit' => 'box-strip'],
            ['name' => 'Angiosar Plus 160/10', 'unit' => 'box-strip'],
            ['name' => 'Lisinopril 20mg', 'unit' => 'box-strip'],
            ['name' => 'Lisinopril 10mg', 'unit' => 'box-strip'],
            ['name' => 'CD Pril 10mg', 'unit' => 'box-strip'],
            ['name' => 'Zinopril 5mg', 'unit' => 'box-strip'],
            ['name' => 'Sinopril', 'unit' => 'box-strip'],
            ['name' => 'Azapril 2.5', 'unit' => 'box-strip'],
            ['name' => 'Corzide 25', 'unit' => 'box-strip'],
            ['name' => 'Torsemide 20', 'unit' => 'box-strip'],
            ['name' => 'Diurex 25', 'unit' => 'box-strip'],
            ['name' => 'Spirdacton 25', 'unit' => 'box-strip'],
            ['name' => 'Spirolon 25', 'unit' => 'box-strip'],
            ['name' => 'Candestan 8mg', 'unit' => 'box-strip'],
            ['name' => 'Candiscot 16', 'unit' => 'box-strip'],
            ['name' => 'Candiscot Plus', 'unit' => 'box-strip'],
            ['name' => 'Candalkan Plus', 'unit' => 'box-strip'],
            ['name' => 'Candestan Plus', 'unit' => 'box-strip'],
            ['name' => 'Coryl 0.25mg', 'unit' => 'box-strip'],
            ['name' => 'Amilosan 50', 'unit' => 'box-strip'],
            ['name' => 'Amilosan C 50/12.5', 'unit' => 'box-strip'],
            ['name' => 'Nifelat 20mg', 'unit' => 'box-strip'],
            ['name' => 'Cozal 25', 'unit' => 'box-strip'],
            ['name' => 'Isorem 10', 'unit' => 'box-strip'],
            ['name' => 'CD Maryl 4', 'unit' => 'box-strip'],
            ['name' => 'Amipride 4', 'unit' => 'box-strip'],
            ['name' => 'Pirmyl 4', 'unit' => 'box-strip'],
            ['name' => 'Getryl 3', 'unit' => 'box-strip'],
            ['name' => 'Glemizal 3', 'unit' => 'box-strip'],
            ['name' => 'Amipride 2', 'unit' => 'box-strip'],
            ['name' => 'Pirmyl I', 'unit' => 'box-strip'],
            ['name' => 'CD Vilda 50', 'unit' => 'box-strip'],
            ['name' => 'Uniphage 500', 'unit' => 'box-strip'],
            ['name' => 'Amifortmin 850', 'unit' => 'box-strip'],
            ['name' => 'Daophage 850', 'unit' => 'box-strip'],
            ['name' => 'CD Formin 1000', 'unit' => 'box-strip'],
            ['name' => 'Prewell', 'unit' => 'box-strip'],
            ['name' => 'Zinc Tab', 'unit' => 'box-strip'],
            ['name' => 'Vilget M 50/850', 'unit' => 'box-strip'],
            ['name' => 'CD Betavert 8', 'unit' => 'box-strip'],
            ['name' => 'Super D3', 'unit' => 'box-strip'],
            ['name' => 'Nephrio', 'unit' => 'box-strip'],
            ['name' => 'Folic Acid', 'unit' => 'box-strip'],
            ['name' => 'Clofinil 75mg', 'unit' => 'box-strip'],
            ['name' => 'Amifenac 100sr', 'unit' => 'box-strip'],
            ['name' => 'Qunine Sulphate', 'unit' => 'box-strip'],
            ['name' => 'Difisal SR 100', 'unit' => 'box-strip'],
            ['name' => 'Balnac 50', 'unit' => 'box-strip'],
            ['name' => 'Paplofen P 50', 'unit' => 'box-strip'],
            ['name' => 'Divido 75mg', 'unit' => 'box-strip'],
            ['name' => 'Gm Menapon 500', 'unit' => 'box-strip'],
            ['name' => 'Miocran Uro', 'unit' => 'box-strip'],
            ['name' => 'Exit 400', 'unit' => 'box-strip-tab'],
            ['name' => 'Vermorex 100', 'unit' => 'box-strip'],
            ['name' => 'CD Mendazole 100', 'unit' => 'bottle'],
            ['name' => 'Mebendazole Susp', 'unit' => 'bottle'],
            ['name' => 'Verem One 500', 'unit' => 'tab'],
            ['name' => 'CD Bralix 5/2.5', 'unit' => 'box-strip'],
            ['name' => 'Tenaxit', 'unit' => null],
            ['name' => 'Colospasmin 100mg', 'unit' => 'box-strip'],
            ['name' => 'Colospasmin 135', 'unit' => 'box-strip'],
            ['name' => 'Amilans 30', 'unit' => 'box-strip'],
            ['name' => 'CD esmol 40mg', 'unit' => 'box-strip'],
            ['name' => 'CD esmol 20mg', 'unit' => 'box-strip'],
            ['name' => 'Esomeprazole 40', 'unit' => 'box-strip'],
            ['name' => 'Pantin 40mg', 'unit' => 'box-strip'],
            ['name' => 'Pantoprazole 40', 'unit' => 'box-strip'],
            ['name' => 'Pantodac 20', 'unit' => 'box-strip'],
            ['name' => 'Omefiz 20', 'unit' => 'box-strip'],
            ['name' => 'Omescot 20', 'unit' => 'box-strip'],
            ['name' => 'CD Flat 125/500', 'unit' => 'box-strip'],
            ['name' => 'Flatidyl', 'unit' => 'box-strip'],
            ['name' => 'Imodal 2mg', 'unit' => 'box-strip'],
            ['name' => 'Domivent 10', 'unit' => 'box-strip'],
            ['name' => 'Perprasol Inj 40', 'unit' => 'vial'],
            ['name' => 'Ondal 8', 'unit' => 'box-strip'],
            ['name' => 'Navoproxin Plus', 'unit' => 'box-strip'],
            ['name' => 'Vitamin B6', 'unit' => 'box-strip'],
            ['name' => 'Senagyl 500', 'unit' => 'box-strip'],
            ['name' => 'Domigest', 'unit' => 'box-strip'],
            ['name' => 'Lact Oral Salu', 'unit' => 'bottle'],
            ['name' => 'Laxopeg Syrup', 'unit' => 'bottle'],
            ['name' => 'Fasigast 500', 'unit' => 'box-strip'],
            ['name' => 'Tinigyl 500', 'unit' => 'box-strip'],
            ['name' => 'Protozole', 'unit' => 'box-strip'],
            ['name' => 'Getal 1000', 'unit' => 'box-strip'],
            ['name' => 'Barkaphon', 'unit' => 'box-strip'],
            ['name' => 'Mylobac', 'unit' => 'box-strip'],
            ['name' => 'Epigesic', 'unit' => 'box-strip'],
            ['name' => 'Meloxidol 15mg', 'unit' => 'box-strip'],
            ['name' => 'Mobizal 15mg', 'unit' => 'box-strip'],
            ['name' => 'Mobizal 7.5mg', 'unit' => 'box-strip'],
            ['name' => 'Ibuscot 200mg', 'unit' => 'box-strip'],
            ['name' => 'Ibu 400mg', 'unit' => 'box-strip'],
            ['name' => 'CD Histin 5', 'unit' => 'box-strip'],
            ['name' => 'CD Levotrizin', 'unit' => 'box-strip'],
            ['name' => 'Ebastel 20', 'unit' => 'box-strip'],
            ['name' => 'Loratadine 10', 'unit' => 'box-strip'],
            ['name' => 'Ebastel 10', 'unit' => 'box-strip'],
            ['name' => 'Furosix', 'unit' => 'amp'],
            ['name' => 'Benzyle Penicillin', 'unit' => 'vial'],
            ['name' => 'Benzathine Bencillin 1.2', 'unit' => 'vial'],
            ['name' => 'CD Duolast 100/60', 'unit' => 'box-strip-tab'],
            ['name' => 'Azafil 100', 'unit' => 'box'],
            ['name' => 'Virecta 100', 'unit' => 'box-strip-tab'],
            ['name' => 'CDafil 20', 'unit' => 'box-strip'],
            ['name' => 'Imutrexate 2.5', 'unit' => 'box-strip'],
            ['name' => 'Myteka 10mg', 'unit' => 'box-strip'],
            ['name' => 'CD ogrel 75mg', 'unit' => 'box-strip'],
            ['name' => 'Decalerto 20', 'unit' => 'box-strip'],
            ['name' => 'Rivaflowva 20', 'unit' => 'box-strip'],
            ['name' => 'Varoxa', 'unit' => 'box-strip'],
            ['name' => 'Nazomed 400', 'unit' => 'box-strip'],
            ['name' => 'Gynoq Mikozal', 'unit' => 'box-strip'],
            ['name' => 'Monicure 200', 'unit' => 'box-strip'],
            ['name' => 'Profulcan 150', 'unit' => 'box-tab'],
            ['name' => 'CD Itrazol 100', 'unit' => 'box-strip'],
            ['name' => 'Grisoral 125', 'unit' => 'box-strip'],
            ['name' => 'Metonorm', 'unit' => 'amp'],
            ['name' => 'CD H Gvine', 'unit' => 'box-strip'],
            ['name' => 'Airrtal 100', 'unit' => 'box-strip'],
            ['name' => 'Aceclo 100', 'unit' => 'box-strip'],
            ['name' => 'Erythromycin', 'unit' => 'box-strip'],
            ['name' => 'CD Claricin 500', 'unit' => 'box-strip'],
            ['name' => 'Nafrafloxacin 500', 'unit' => 'box-strip'],
            ['name' => 'Unitariqin 400', 'unit' => 'box-strip'],
            ['name' => 'Wafranor 400', 'unit' => 'box-strip'],
            ['name' => 'Levoscot 500', 'unit' => 'box-strip'],
            ['name' => 'CD Linozid', 'unit' => 'box-strip'],
            ['name' => 'Xiotil 500', 'unit' => 'box-strip'],
            ['name' => 'Zetum 500', 'unit' => 'box-strip'],
            ['name' => 'GM Zirocin 500', 'unit' => 'box-strip'],
            ['name' => 'Zithroset 500', 'unit' => 'box-strip'],
            ['name' => 'Amoclan 375mg', 'unit' => 'box-strip'],
            ['name' => 'Clavimax 625', 'unit' => 'box-strip'],
            ['name' => 'Novamentin 625', 'unit' => 'box-strip'],
            ['name' => 'Amiclav 1000mg', 'unit' => 'box-strip'],
            ['name' => 'Novamentin 1000mg', 'unit' => 'box-strip'],
            ['name' => 'Clovimax 457', 'unit' => 'bottle'],
            ['name' => 'Sky Gloves', 'unit' => 'box-strip'],
            ['name' => 'Depox 200', 'unit' => 'box-strip'],
            ['name' => 'Olabenz 5mg', 'unit' => 'box-strip'],
            ['name' => 'Famila', 'unit' => 'box-strip'],
            ['name' => 'Microgenest', 'unit' => 'box-strip'],
            ['name' => 'Sogestedin', 'unit' => 'box-strip'],
            ['name' => 'T4 Thyro 100', 'unit' => 'box-strip'],
            ['name' => 'Neo Mecrazole', 'unit' => 'box-strip'],
            ['name' => 'Carbimazole 5', 'unit' => 'box-strip'],
            ['name' => 'Novofen 10mg', 'unit' => 'box-strip'],
            ['name' => 'Dosin 4mg', 'unit' => 'box-strip'],
            ['name' => 'Xalgetz 0.4', 'unit' => 'box-strip'],
            ['name' => 'Prostacin 0.4', 'unit' => 'box-strip'],
            ['name' => 'Prostride 5mg', 'unit' => 'box-strip'],
            ['name' => 'Carduduex 4', 'unit' => 'box-strip'],
            ['name' => 'Tobrin 03%', 'unit' => 'drop'],
            ['name' => 'Iventi D', 'unit' => 'drop'],
            ['name' => 'Epifenac 1mg', 'unit' => 'drop'],
            ['name' => 'Alphabrinzima', 'unit' => 'drop'],
            ['name' => 'Zonacip', 'unit' => 'drop'],
            ['name' => 'Neo Pol E.d', 'unit' => 'drop'],
            ['name' => 'Normo Tears', 'unit' => 'drop'],
            ['name' => 'Brimodin 0.2%', 'unit' => 'drop'],
            ['name' => 'Brimosalm 0.15', 'unit' => 'drop'],
            ['name' => 'Prisoline', 'unit' => 'drop'],
            ['name' => 'Dextrobac', 'unit' => 'drop'],
            ['name' => 'Oxcin', 'unit' => 'drop'],
            ['name' => 'Nostamine', 'unit' => 'drop'],
            ['name' => 'Atazol 0.05%', 'unit' => 'drop'],
            ['name' => 'Antazol', 'unit' => 'drop'],
            ['name' => 'Ojoblick', 'unit' => 'drop'],
            ['name' => 'Cortis 250', 'unit' => 'inh'],
            ['name' => 'No Uric 300mg', 'unit' => 'box-strip'],
            ['name' => 'No Uric 100mg', 'unit' => 'box-strip'],
            ['name' => 'CD Anxitin 20', 'unit' => 'box-strip'],
            ['name' => 'Amirol 10mg', 'unit' => 'box-strip'],
            ['name' => 'Haloxen 5mg', 'unit' => 'box-strip'],
            ['name' => 'Travast 0.004', 'unit' => 'drop'],
            ['name' => 'Travast Plus', 'unit' => 'drop'],
            ['name' => 'Ramedazolmide', 'unit' => 'drop'],
            ['name' => 'Acetazolamide 250', 'unit' => 'box-strip'],
            ['name' => 'Amitriptyline', 'unit' => 'box-strip'],
            ['name' => 'Topilept 100', 'unit' => 'box-strip'],
            ['name' => 'CD Parkidopa 250', 'unit' => 'box-strip'],
            ['name' => 'CD Rispdon 2mg', 'unit' => 'box-strip'],
            ['name' => 'CD Rispdon 4mg', 'unit' => 'box-strip'],
            ['name' => 'Tabunex 0.05%', 'unit' => 'box-strip'],
            ['name' => 'Dexatrol', 'unit' => 'tab'],
            ['name' => 'Nasosal Plus', 'unit' => 'inh'],
            ['name' => 'Nasosal Hyper', 'unit' => 'inh'],
            ['name' => 'Ipratom 250/2', 'unit' => 'box-strip'],
            ['name' => 'CD Cetam 500', 'unit' => 'box-strip'],
            ['name' => 'Levnoseiz 500', 'unit' => 'box-strip'],
            ['name' => 'Levetiracetam 500', 'unit' => 'box-strip'],
            ['name' => 'Levnoseiz 1000', 'unit' => 'box-strip'],
            ['name' => 'Lanzapine 10mg', 'unit' => 'box-strip'],
            ['name' => 'Excelsa 20mg', 'unit' => 'box-strip'],
            ['name' => 'Citanew 10mg', 'unit' => 'box-strip'],
            ['name' => 'Tolopram 40mg', 'unit' => 'box-strip'],
            ['name' => 'Depretin 40mg', 'unit' => 'box-strip'],
            ['name' => 'Valpromeal Syrup', 'unit' => 'bottle'],
            ['name' => 'Depavalpolem Syrup', 'unit' => 'bottle'],
            ['name' => 'Depox 500', 'unit' => 'box-strip'],
            ['name' => 'CD Gapentin 100', 'unit' => 'box-strip'],
            ['name' => 'Gabix 300mg', 'unit' => 'box-strip'],
            ['name' => 'Gabica 75mg', 'unit' => 'box-strip'],
            ['name' => 'Carbatec 400sr', 'unit' => 'box-strip'],
            ['name' => 'Hayalepsin 200', 'unit' => 'box-strip'],
            ['name' => 'Clavimax 1g', 'unit' => 'box-strip'],
            ['name' => 'Amiclav 625', 'unit' => 'box-strip'],
            ['name' => 'Predilone 5mg', 'unit' => 'box-strip'],
            ['name' => 'Predinsol CD 5', 'unit' => 'box-strip'],
            ['name' => 'Dr Altag', 'unit' => 'drop'],
            ['name' => 'N.S Nasal Drop', 'unit' => 'drop'],
            ['name' => 'Clove Oil', 'unit' => 'drop'],
            ['name' => 'Flancogyl 500', 'unit' => 'box-strip'],
            ['name' => 'Amindazol 500', 'unit' => 'box-strip'],
            ['name' => 'Multi Vit', 'unit' => 'box-strip'],
            ['name' => 'Proditil 100ps', 'unit' => 'box-strip'],
            ['name' => 'Azimax 250', 'unit' => 'box-strip'],
            ['name' => 'Electroscot', 'unit' => 'pic'],
            ['name' => 'S Nubeno 5ml', 'unit' => 'drop'],
            ['name' => 'Avalon Cream', 'unit' => 'box'],
            ['name' => 'Spotless Face Cream', 'unit' => 'tub'],
            ['name' => 'CD Anagrow', 'unit' => 'box'],
            ['name' => 'Baby Talcun Powder', 'unit' => 'pic'],
            ['name' => 'Zinc Nova', 'unit' => 'pic'],
            ['name' => 'Newday Mouth Wash', 'unit' => 'bottle'],
            ['name' => 'Orex Spray', 'unit' => 'spray'],
            ['name' => 'Smarth Xiden', 'unit' => 'pic'],
            ['name' => 'Clear Plus Soap', 'unit' => 'pic'],
            ['name' => 'Saliderm Soap', 'unit' => 'pic'],
            ['name' => 'Scalix Soap', 'unit' => 'pic
