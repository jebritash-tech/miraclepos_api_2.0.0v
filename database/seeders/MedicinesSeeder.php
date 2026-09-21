<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicinesSeeder extends Seeder
{
    /**
     * Seed the complete medicine catalogue.
     *
     * Source:
     * Medicines.xlsx
     *
     * IMPORTANT:
     * - 0.5mg is NOT a selling unit.
     * - Package conversion factors are not supplied by Excel.
     * - Therefore factor = 1 initially.
     * - The last valid unit in a compound definition is the base unit.
     */
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Master Units
            |--------------------------------------------------------------------------
            |
            | These are the actual selling units used by the system.
            |
            | 0.5mg is intentionally NOT included.
            | It is a concentration/strength, not a selling unit.
            |
            */

            $units = [
                'pc'   => [
                    'name'   => 'Piece',
                    'symbol' => 'pc',
                ],

                'str'  => [
                    'name'   => 'Strip',
                    'symbol' => 'str',
                ],

                'box'  => [
                    'name'   => 'Box',
                    'symbol' => 'box',
                ],

                'bot'  => [
                    'name'   => 'Bottle',
                    'symbol' => 'bot',
                ],

                'drop' => [
                    'name'   => 'Drop',
                    'symbol' => 'drop',
                ],

                'vial' => [
                    'name'   => 'Vial',
                    'symbol' => 'vial',
                ],

                'tube' => [
                    'name'   => 'Tube',
                    'symbol' => 'tube',
                ],

                'amp'  => [
                    'name'   => 'Ampoule',
                    'symbol' => 'amp',
                ],

                'inh'  => [
                    'name'   => 'Inhaler',
                    'symbol' => 'inh',
                ],

                'tab'  => [
                    'name'   => 'Tablet',
                    'symbol' => 'tab',
                ],

                'drip' => [
                    'name'   => 'Drip',
                    'symbol' => 'drip',
                ],

                'spray' => [
                    'name'   => 'Spray',
                    'symbol' => 'spray',
                ],

                'set' => [
                    'name'   => 'Set',
                    'symbol' => 'set',
                ],
            ];


            /*
            |--------------------------------------------------------------------------
            | Create / Update Master Units
            |--------------------------------------------------------------------------
            */

            foreach ($units as $symbol => $unit) {

                $existing = DB::table('units')
                    ->where('symbol', $symbol)
                    ->first();

                if ($existing) {

                    DB::table('units')
                        ->where('id', $existing->id)
                        ->update([
                            'name'       => $unit['name'],
                            'symbol'     => $unit['symbol'],
                            'active'     => 1,
                            'updated_at' => now(),
                        ]);

                } else {

                    DB::table('units')->insert([
                        'name'       => $unit['name'],
                        'symbol'     => $unit['symbol'],
                        'active'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Unit Mapping
            |--------------------------------------------------------------------------
            |
            | Maps the notation used in Medicines.xlsx
            | to the actual units table symbols.
            |
            */

            $unitMap = [

                'box'    => 'box',

                'strip'  => 'str',

                'pic'    => 'pc',
                'piece'  => 'pc',
                'pc'     => 'pc',

                'bottle' => 'bot',
                'bot'    => 'bot',

                'drop'   => 'drop',

                'vial'   => 'vial',

                'tub'    => 'tube',
                'tube'   => 'tube',

                'amp'    => 'amp',
                'ampul'  => 'amp',

                'inh'    => 'inh',

                'tab'    => 'tab',

                'drip'   => 'drip',

                'spray'  => 'spray',

                'set'    => 'set',

                /*
                 * Intentionally NOT mapping:
                 *
                 * 0.5mg
                 *
                 * because it is not a selling unit.
                 */
            ];


            /*
            |--------------------------------------------------------------------------
            | Medicine Catalogue
            |--------------------------------------------------------------------------
            |
            | PART 1
            |
            */

            $medicines =  [

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
                ['name' => 'Omevox', 'unit' => 'box-strip'],
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

                // =========================================================
                // PART 2 CONTINUES HERE
                // =========================================================
                ['name' => 'Pantoprazole 40', 'unit' => 'box-strip'],
                ['name' => 'Pantozol 40', 'unit' => 'box-strip'],
                ['name' => 'Pantoprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Razo 20', 'unit' => 'box-strip'],
                ['name' => 'Rabeprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Esopral 40', 'unit' => 'box-strip'],
                ['name' => 'Esopral 20', 'unit' => 'box-strip'],
                ['name' => 'Esomeprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Losec 20', 'unit' => 'box-strip'],
                ['name' => 'Omeprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Gastrazole 20', 'unit' => 'box-strip'],
                ['name' => 'Famotidine 20', 'unit' => 'box-strip'],
                ['name' => 'Famodin 20', 'unit' => 'box-strip'],
                ['name' => 'Domperidone 10', 'unit' => 'box-strip'],
                ['name' => 'Motilium 10', 'unit' => 'box-strip'],
                ['name' => 'Metoclopramide 10', 'unit' => 'box-strip'],
                ['name' => 'Primperan 10', 'unit' => 'box-strip'],
                ['name' => 'Buscopan 10', 'unit' => 'box-strip'],
                ['name' => 'Colospasmin 100', 'unit' => 'box-strip'],
                ['name' => 'Mebeverine 135', 'unit' => 'box-strip'],
                ['name' => 'Loperamide 2', 'unit' => 'box-strip'],
                ['name' => 'Imodium 2', 'unit' => 'box-strip'],
                ['name' => 'ORS', 'unit' => 'box-strip'],
                ['name' => 'Hydralyte', 'unit' => 'box'],
                ['name' => 'Smecta', 'unit' => 'box-strip'],
                ['name' => 'Lactulose', 'unit' => 'bottle'],
                ['name' => 'Duphalac', 'unit' => 'bottle'],
                ['name' => 'Bisacodyl 5', 'unit' => 'box-strip'],
                ['name' => 'Dulcolax 5', 'unit' => 'box-strip'],
                ['name' => 'Senna', 'unit' => 'box-strip'],
                ['name' => 'Glycerin Supp', 'unit' => 'box-supp'],
                ['name' => 'Paracetamol 500', 'unit' => 'box-strip'],
                ['name' => 'Panadol 500', 'unit' => 'box-strip'],
                ['name' => 'Paracetamol 1000', 'unit' => 'box-strip'],
                ['name' => 'Panadol Extra', 'unit' => 'box-strip'],
                ['name' => 'Ibuprofen 400', 'unit' => 'box-strip'],
                ['name' => 'Ibuprofen 200', 'unit' => 'box-strip'],
                ['name' => 'Brufen 400', 'unit' => 'box-strip'],
                ['name' => 'Diclofenac 50', 'unit' => 'box-strip'],
                ['name' => 'Voltaren 50', 'unit' => 'box-strip'],
                ['name' => 'Diclofenac SR 100', 'unit' => 'box-strip'],
                ['name' => 'Voltaren SR 100', 'unit' => 'box-strip'],
                ['name' => 'Naproxen 500', 'unit' => 'box-strip'],
                ['name' => 'Ketoprofen 100', 'unit' => 'box-strip'],
                ['name' => 'Celecoxib 200', 'unit' => 'box-strip'],
                ['name' => 'Celebrex 200', 'unit' => 'box-strip'],
                ['name' => 'Meloxicam 15', 'unit' => 'box-strip'],
                ['name' => 'Meloxicam 7.5', 'unit' => 'box-strip'],
                ['name' => 'Piroxicam 20', 'unit' => 'box-strip'],
                ['name' => 'Tramadol 50', 'unit' => 'box-strip'],
                ['name' => 'Tramadol 100', 'unit' => 'box-strip'],
                ['name' => 'Gabapentin 300', 'unit' => 'box-strip'],
                ['name' => 'Pregabalin 75', 'unit' => 'box-strip'],
                ['name' => 'Pregabalin 150', 'unit' => 'box-strip'],
                ['name' => 'Amitriptyline 25', 'unit' => 'box-strip'],
                ['name' => 'Amitriptyline 10', 'unit' => 'box-strip'],
                ['name' => 'Carbamazepine 200', 'unit' => 'box-strip'],
                ['name' => 'Valproate 200', 'unit' => 'box-strip'],
                ['name' => 'Sodium Valproate 500', 'unit' => 'box-strip'],
                ['name' => 'Levetiracetam 500', 'unit' => 'box-strip'],
                ['name' => 'Phenytoin 100', 'unit' => 'box-strip'],
                ['name' => 'Diazepam 5', 'unit' => 'box-strip'],
                ['name' => 'Clonazepam 0.5', 'unit' => 'box-strip'],
                ['name' => 'Sertraline 50', 'unit' => 'box-strip'],
                ['name' => 'Fluoxetine 20', 'unit' => 'box-strip'],
                ['name' => 'Amitriptyline 25', 'unit' => 'box-strip'],
                ['name' => 'Olanzapine 10', 'unit' => 'box-strip'],
                ['name' => 'Risperidone 2', 'unit' => 'box-strip'],
                ['name' => 'Quetiapine 25', 'unit' => 'box-strip'],
                ['name' => 'Haloperidol 5', 'unit' => 'box-strip'],
                ['name' => 'Amoxicillin 500', 'unit' => 'box-strip'],
                ['name' => 'Amoxicillin 250', 'unit' => 'box-strip'],
                ['name' => 'Amoxicillin Susp', 'unit' => 'bottle'],
                ['name' => 'Augmentin 625', 'unit' => 'box-strip'],
                ['name' => 'Augmentin 1g', 'unit' => 'box-strip'],
                ['name' => 'Cefixime 400', 'unit' => 'box-strip'],
                ['name' => 'Cefixime 200', 'unit' => 'box-strip'],
                ['name' => 'Cefuroxime 500', 'unit' => 'box-strip'],
                ['name' => 'Cefuroxime 250', 'unit' => 'box-strip'],
                ['name' => 'Ceftriaxone 1g', 'unit' => 'vial'],
                ['name' => 'Ceftriaxone 500mg', 'unit' => 'vial'],
                ['name' => 'Cefotaxime 1g', 'unit' => 'vial'],
                ['name' => 'Ceftazidime 1g', 'unit' => 'vial'],
                ['name' => 'Meropenem 1g', 'unit' => 'vial'],
                ['name' => 'Metronidazole 500', 'unit' => 'box-strip'],
                ['name' => 'Metronidazole Susp', 'unit' => 'bottle'],
                ['name' => 'Azithromycin 500', 'unit' => 'box-strip'],
                ['name' => 'Azithromycin 250', 'unit' => 'box-strip'],
                ['name' => 'Azithromycin Susp', 'unit' => 'bottle'],
                ['name' => 'Clarithromycin 500', 'unit' => 'box-strip'],
                ['name' => 'Ciprofloxacin 500', 'unit' => 'box-strip'],
                ['name' => 'Ciprofloxacin 250', 'unit' => 'box-strip'],
                ['name' => 'Levofloxacin 500', 'unit' => 'box-strip'],
                ['name' => 'Levofloxacin 750', 'unit' => 'box-strip'],
                ['name' => 'Doxycycline 100', 'unit' => 'box-strip'],
                ['name' => 'Tetracycline 250', 'unit' => 'box-strip'],
                ['name' => 'Clindamycin 300', 'unit' => 'box-strip'],
                ['name' => 'Co-trimoxazole', 'unit' => 'box-strip'],
                ['name' => 'Nitrofurantoin 100', 'unit' => 'box-strip'],
                ['name' => 'Fosfomycin 3g', 'unit' => 'box'],
                ['name' => 'Fluconazole 150', 'unit' => 'box-strip'],
                ['name' => 'Ketoconazole 200', 'unit' => 'box-strip'],
                ['name' => 'Itraconazole 100', 'unit' => 'box-strip'],
                ['name' => 'Acyclovir 400', 'unit' => 'box-strip'],
                ['name' => 'Acyclovir 200', 'unit' => 'box-strip'],
                ['name' => 'Valacyclovir 500', 'unit' => 'box-strip'],
                ['name' => 'Nystatin', 'unit' => 'box-strip'],
                ['name' => 'Nystatin Susp', 'unit' => 'bottle'],
                ['name' => 'Clotrimazole', 'unit' => 'box-strip'],
                ['name' => 'Miconazole', 'unit' => 'tube'],
                ['name' => 'Hydrocortisone 1%', 'unit' => 'tube'],
                ['name' => 'Betamethasone', 'unit' => 'tube'],
                ['name' => 'Betnovate', 'unit' => 'tube'],
                ['name' => 'Fucidin', 'unit' => 'tube'],
                ['name' => 'Mupirocin', 'unit' => 'tube'],
                ['name' => 'Neomycin', 'unit' => 'tube'],
                ['name' => 'Clotrimazole Cream', 'unit' => 'tube'],
                ['name' => 'Clotrimazole Vaginal', 'unit' => 'box-strip'],
                ['name' => 'Metronidazole Vaginal', 'unit' => 'box-strip'],
                ['name' => 'Miconazole Vaginal', 'unit' => 'box-strip'],
                ['name' => 'Fluconazole 150', 'unit' => 'box-strip'],
                ['name' => 'Cetirizine 10', 'unit' => 'box-strip'],
                ['name' => 'Loratadine 10', 'unit' => 'box-strip'],
                ['name' => 'Desloratadine 5', 'unit' => 'box-strip'],
                ['name' => 'Fexofenadine 120', 'unit' => 'box-strip'],
                ['name' => 'Fexofenadine 180', 'unit' => 'box-strip'],
                ['name' => 'Chlorpheniramine 4', 'unit' => 'box-strip'],
                ['name' => 'Promethazine 25', 'unit' => 'box-strip'],
                ['name' => 'Montelukast 10', 'unit' => 'box-strip'],
                ['name' => 'Montelukast 5', 'unit' => 'box-strip'],
                ['name' => 'Salbutamol', 'unit' => 'inh'],
                ['name' => 'Ventolin', 'unit' => 'inh'],
                ['name' => 'Budesonide', 'unit' => 'inh'],
                ['name' => 'Symbicort', 'unit' => 'inh'],
                ['name' => 'Seretide', 'unit' => 'inh'],
                ['name' => 'Beclomethasone', 'unit' => 'inh'],
                ['name' => 'Ipratropium', 'unit' => 'inh'],
                ['name' => 'Combivent', 'unit' => 'inh'],
                ['name' => 'Salbutamol Syrup', 'unit' => 'bottle'],
                ['name' => 'Theophylline 200', 'unit' => 'box-strip'],
                ['name' => 'Aminophylline', 'unit' => 'amp'],
                ['name' => 'Dexamethasone', 'unit' => 'amp'],
                ['name' => 'Hydrocortisone', 'unit' => 'vial'],
                ['name' => 'Prednisolone 5', 'unit' => 'box-strip'],
                ['name' => 'Prednisolone 20', 'unit' => 'box-strip'],
                ['name' => 'Methylprednisolone', 'unit' => 'vial'],
                ['name' => 'Betamethasone', 'unit' => 'amp'],
                ['name' => 'Diclofenac Injection', 'unit' => 'amp'],
                ['name' => 'Ketorolac Injection', 'unit' => 'amp'],
                ['name' => 'Tramadol Injection', 'unit' => 'amp'],
                ['name' => 'Metoclopramide Injection', 'unit' => 'amp'],
                ['name' => 'Ondansetron Injection', 'unit' => 'amp'],
                ['name' => 'Promethazine Injection', 'unit' => 'amp'],
                ['name' => 'Atropine', 'unit' => 'amp'],
                ['name' => 'Adrenaline', 'unit' => 'amp'],
                ['name' => 'Furosemide', 'unit' => 'amp'],
                ['name' => 'Omeprazole Injection', 'unit' => 'vial'],
                ['name' => 'Pantoprazole Injection', 'unit' => 'vial'],
                ['name' => 'Amikacin', 'unit' => 'vial'],
                ['name' => 'Gentamicin', 'unit' => 'amp'],
                ['name' => 'Heparin', 'unit' => 'vial'],
                ['name' => 'Enoxaparin', 'unit' => 'box'],
                ['name' => 'Insulin Regular', 'unit' => 'vial'],
                ['name' => 'Insulin NPH', 'unit' => 'vial'],
                ['name' => 'Metformin 500', 'unit' => 'box-strip'],
                ['name' => 'Metformin 850', 'unit' => 'box-strip'],
                ['name' => 'Metformin 1000', 'unit' => 'box-strip'],
                ['name' => 'Glimepiride 2', 'unit' => 'box-strip'],
                ['name' => 'Glimepiride 4', 'unit' => 'box-strip'],
                ['name' => 'Gliclazide 80', 'unit' => 'box-strip'],
                ['name' => 'Sitagliptin 100', 'unit' => 'box-strip'],
                ['name' => 'Vildagliptin 50', 'unit' => 'box-strip'],
                ['name' => 'Empagliflozin 10', 'unit' => 'box-strip'],
                ['name' => 'Empagliflozin 25', 'unit' => 'box-strip'],
                ['name' => 'Dapagliflozin 10', 'unit' => 'box-strip'],
                ['name' => 'Dapagliflozin 5', 'unit' => 'box-strip'],
                ['name' => 'Atorvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Atorvastatin 40', 'unit' => 'box-strip'],
                ['name' => 'Rosuvastatin 10', 'unit' => 'box-strip'],
                ['name' => 'Rosuvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Simvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Aspirin 75', 'unit' => 'box-strip'],
                ['name' => 'Aspirin 100', 'unit' => 'box-strip'],
                ['name' => 'Clopidogrel 75', 'unit' => 'box-strip'],
                ['name' => 'Bisoprolol 5', 'unit' => 'box-strip'],
                ['name' => 'Bisoprolol 10', 'unit' => 'box-strip'],
                ['name' => 'Amlodipine 5', 'unit' => 'box-strip'],
                ['name' => 'Amlodipine 10', 'unit' => 'box-strip'],
                ['name' => 'Losartan 50', 'unit' => 'box-strip'],
                ['name' => 'Losartan 100', 'unit' => 'box-strip'],
                ['name' => 'Valsartan 80', 'unit' => 'box-strip'],
                ['name' => 'Valsartan 160', 'unit' => 'box-strip'],
                ['name' => 'Enalapril 10', 'unit' => 'box-strip'],
                ['name' => 'Enalapril 20', 'unit' => 'box-strip'],
                ['name' => 'Lisinopril 10', 'unit' => 'box-strip'],
                ['name' => 'Lisinopril 20', 'unit' => 'box-strip'],
                ['name' => 'Hydrochlorothiazide 25', 'unit' => 'box-strip'],
                ['name' => 'Furosemide 40', 'unit' => 'box-strip'],
                ['name' => 'Spironolactone 25', 'unit' => 'box-strip'],
                ['name' => 'Spironolactone 100', 'unit' => 'box-strip'],
                ['name' => 'Nifedipine 20', 'unit' => 'box-strip'],
                ['name' => 'Diltiazem 60', 'unit' => 'box-strip'],
                ['name' => 'Verapamil 40', 'unit' => 'box-strip'],
                ['name' => 'Isosorbide 10', 'unit' => 'box-strip'],
                ['name' => 'Nitroglycerin', 'unit' => 'box-strip'],
                ['name' => 'Xalgetz 0.4', 'unit' => 'box-strip'],
                // Part 3
                ['name' => 'Prostacin 0.4', 'unit' => 'box-strip'],
                ['name' => 'Prostride 5mg', 'unit' => 'box-strip'],
                ['name' => 'Carduduex 4', 'unit' => 'box-strip'],
                ['name' => 'Tobrin 03%', 'unit' => 'drop'],
                ['name' => 'Tobrin-D', 'unit' => 'drop'],
                ['name' => 'Ciprofloxacin Eye Drop', 'unit' => 'drop'],
                ['name' => 'Maxitrol', 'unit' => 'drop'],
                ['name' => 'Dexatrol', 'unit' => 'drop'],
                ['name' => 'Chloramphenicol Eye Drop', 'unit' => 'drop'],
                ['name' => 'Refresh Tears', 'unit' => 'drop'],
                ['name' => 'Systane Ultra', 'unit' => 'drop'],
                ['name' => 'Lacri-Lube', 'unit' => 'tube'],
                ['name' => 'Timolol 0.5%', 'unit' => 'drop'],
                ['name' => 'Dorzolamide', 'unit' => 'drop'],
                ['name' => 'Latanoprost', 'unit' => 'drop'],
                ['name' => 'Travoprost', 'unit' => 'drop'],
                ['name' => 'Brimonidine', 'unit' => 'drop'],
                ['name' => 'Tafluprost', 'unit' => 'drop'],
                ['name' => 'Ketorolac Eye Drop', 'unit' => 'drop'],
                ['name' => 'Nepafenac', 'unit' => 'drop'],
                ['name' => 'Olopatadine', 'unit' => 'drop'],
                ['name' => 'Ketotifen', 'unit' => 'drop'],
                ['name' => 'Moxifloxacin Eye Drop', 'unit' => 'drop'],
                ['name' => 'Ofloxacin Eye Drop', 'unit' => 'drop'],
                ['name' => 'Fusidic Acid Eye Drop', 'unit' => 'drop'],
                ['name' => 'Aciclovir Eye Ointment', 'unit' => 'tube'],
                ['name' => 'Cromoglycate Eye Drop', 'unit' => 'drop'],
                ['name' => 'Artificial Tears', 'unit' => 'drop'],
                ['name' => 'Prednisolone Eye Drop', 'unit' => 'drop'],
                ['name' => 'Dexamethasone Eye Drop', 'unit' => 'drop'],
                ['name' => 'Tropicamide', 'unit' => 'drop'],
                ['name' => 'Phenylephrine Eye Drop', 'unit' => 'drop'],
                ['name' => 'Atropine Eye Drop', 'unit' => 'drop'],
                ['name' => 'Cyclopentolate', 'unit' => 'drop'],
                ['name' => 'Albendazole 400', 'unit' => 'box-strip'],
                ['name' => 'Mebendazole 100', 'unit' => 'box-strip'],
                ['name' => 'Mebendazole 500', 'unit' => 'box-strip'],
                ['name' => 'Ivermectin 6mg', 'unit' => 'box-strip'],
                ['name' => 'Praziquantel 600', 'unit' => 'box-strip'],
                ['name' => 'Metronidazole 250', 'unit' => 'box-strip'],
                ['name' => 'Tinidazole 500', 'unit' => 'box-strip'],
                ['name' => 'Secnidazole 1g', 'unit' => 'box-strip'],
                ['name' => 'Nitazoxanide 500', 'unit' => 'box-strip'],
                ['name' => 'Nitazoxanide Susp', 'unit' => 'bottle'],
                ['name' => 'Artemether Lumefantrine', 'unit' => 'box-strip'],
                ['name' => 'Coartem', 'unit' => 'box-strip'],
                ['name' => 'Fansidar', 'unit' => 'box-strip'],
                ['name' => 'Sulfadoxine Pyrimethamine', 'unit' => 'box-strip'],
                ['name' => 'Quinine', 'unit' => 'box-strip'],
                ['name' => 'Quinine Injection', 'unit' => 'amp'],
                ['name' => 'Artesunate', 'unit' => 'vial'],
                ['name' => 'Artesunate Injection', 'unit' => 'vial'],
                ['name' => 'Primaquine', 'unit' => 'box-strip'],
                ['name' => 'Doxycycline 100mg', 'unit' => 'box-strip'],
                ['name' => 'Clindamycin 150mg', 'unit' => 'box-strip'],
                ['name' => 'Clindamycin 300mg', 'unit' => 'box-strip'],
                ['name' => 'Linezolid 600', 'unit' => 'box-strip'],
                ['name' => 'Vancomycin 500', 'unit' => 'vial'],
                ['name' => 'Vancomycin 1g', 'unit' => 'vial'],
                ['name' => 'Piperacillin Tazobactam', 'unit' => 'vial'],
                ['name' => 'Cefepime 1g', 'unit' => 'vial'],
                ['name' => 'Cefepime 2g', 'unit' => 'vial'],
                ['name' => 'Cefoperazone', 'unit' => 'vial'],
                ['name' => 'Cefotaxime', 'unit' => 'vial'],
                ['name' => 'Ceftazidime', 'unit' => 'vial'],
                ['name' => 'Cefazolin', 'unit' => 'vial'],
                ['name' => 'Ampicillin 500', 'unit' => 'vial'],
                ['name' => 'Ampicillin 1g', 'unit' => 'vial'],
                ['name' => 'Benzylpenicillin', 'unit' => 'vial'],
                ['name' => 'Procaine Penicillin', 'unit' => 'vial'],
                ['name' => 'Benzathine Penicillin', 'unit' => 'vial'],
                ['name' => 'Gentamicin 80mg', 'unit' => 'amp'],
                ['name' => 'Amikacin 500mg', 'unit' => 'vial'],
                ['name' => 'Metronidazole IV', 'unit' => 'drip'],
                ['name' => 'Ciprofloxacin IV', 'unit' => 'drip'],
                ['name' => 'Levofloxacin IV', 'unit' => 'drip'],
                ['name' => 'Fluconazole IV', 'unit' => 'drip'],
                ['name' => 'Mannitol', 'unit' => 'drip'],
                ['name' => 'Normal Saline', 'unit' => 'drip'],
                ['name' => 'Ringer Lactate', 'unit' => 'drip'],
                ['name' => 'Dextrose 5%', 'unit' => 'drip'],
                ['name' => 'Dextrose 10%', 'unit' => 'drip'],
                ['name' => 'Dextrose 50%', 'unit' => 'amp'],
                ['name' => 'Potassium Chloride', 'unit' => 'amp'],
                ['name' => 'Calcium Gluconate', 'unit' => 'amp'],
                ['name' => 'Magnesium Sulphate', 'unit' => 'amp'],
                ['name' => 'Sodium Bicarbonate', 'unit' => 'amp'],
                ['name' => 'Lidocaine', 'unit' => 'amp'],
                ['name' => 'Lidocaine Gel', 'unit' => 'tube'],
                ['name' => 'Bupivacaine', 'unit' => 'amp'],
                ['name' => 'Propofol', 'unit' => 'amp'],
                ['name' => 'Midazolam', 'unit' => 'amp'],
                ['name' => 'Ketamine', 'unit' => 'vial'],
                ['name' => 'Morphine', 'unit' => 'amp'],
                ['name' => 'Naloxone', 'unit' => 'amp'],
                ['name' => 'Phytomenadione', 'unit' => 'amp'],
                ['name' => 'Tranexamic Acid', 'unit' => 'amp'],
                ['name' => 'Oxytocin', 'unit' => 'amp'],
                ['name' => 'Misoprostol', 'unit' => 'box-strip'],
                ['name' => 'Ergometrine', 'unit' => 'amp'],
                ['name' => 'Methyldopa 250', 'unit' => 'box-strip'],
                ['name' => 'Methyldopa 500', 'unit' => 'box-strip'],
                ['name' => 'Labetalol', 'unit' => 'box-strip'],
                ['name' => 'Nifedipine 10', 'unit' => 'box-strip'],
                ['name' => 'Nifedipine 20', 'unit' => 'box-strip'],
                ['name' => 'Atenolol 50', 'unit' => 'box-strip'],
                ['name' => 'Atenolol 100', 'unit' => 'box-strip'],
                ['name' => 'Propranolol 40', 'unit' => 'box-strip'],
                ['name' => 'Carvedilol 6.25', 'unit' => 'box-strip'],
                ['name' => 'Carvedilol 12.5', 'unit' => 'box-strip'],
                ['name' => 'Carvedilol 25', 'unit' => 'box-strip'],
                ['name' => 'Amlodipine 5mg', 'unit' => 'box-strip'],
                ['name' => 'Amlodipine 10mg', 'unit' => 'box-strip'],
                ['name' => 'Amlodipine Valsartan', 'unit' => 'box-strip'],
                ['name' => 'Losartan 50mg', 'unit' => 'box-strip'],
                ['name' => 'Losartan 100mg', 'unit' => 'box-strip'],
                ['name' => 'Losartan HCT', 'unit' => 'box-strip'],
                ['name' => 'Valsartan 80mg', 'unit' => 'box-strip'],
                ['name' => 'Valsartan 160mg', 'unit' => 'box-strip'],
                ['name' => 'Valsartan HCT', 'unit' => 'box-strip'],
                ['name' => 'Telmisartan 40', 'unit' => 'box-strip'],
                ['name' => 'Telmisartan 80', 'unit' => 'box-strip'],
                ['name' => 'Telmisartan HCT', 'unit' => 'box-strip'],
                ['name' => 'Irbesartan 150', 'unit' => 'box-strip'],
                ['name' => 'Irbesartan 300', 'unit' => 'box-strip'],
                ['name' => 'Candesartan 8', 'unit' => 'box-strip'],
                ['name' => 'Candesartan 16', 'unit' => 'box-strip'],
                ['name' => 'Candesartan 32', 'unit' => 'box-strip'],
                ['name' => 'Ramipril 5', 'unit' => 'box-strip'],
                ['name' => 'Ramipril 10', 'unit' => 'box-strip'],
                ['name' => 'Perindopril 5', 'unit' => 'box-strip'],
                ['name' => 'Perindopril 10', 'unit' => 'box-strip'],
                ['name' => 'Indapamide 1.5', 'unit' => 'box-strip'],
                ['name' => 'Hydrochlorothiazide 12.5', 'unit' => 'box-strip'],
                ['name' => 'Furosemide 20', 'unit' => 'box-strip'],
                ['name' => 'Furosemide 40', 'unit' => 'box-strip'],
                ['name' => 'Furosemide Injection', 'unit' => 'amp'],
                ['name' => 'Spironolactone 25', 'unit' => 'box-strip'],
                ['name' => 'Spironolactone 50', 'unit' => 'box-strip'],
                ['name' => 'Spironolactone 100', 'unit' => 'box-strip'],
                ['name' => 'Hydralazine', 'unit' => 'box-strip'],
                ['name' => 'Isosorbide Dinitrate 10', 'unit' => 'box-strip'],
                ['name' => 'Isosorbide Mononitrate 20', 'unit' => 'box-strip'],
                ['name' => 'Isosorbide Mononitrate 40', 'unit' => 'box-strip'],
                ['name' => 'Nitroglycerin', 'unit' => 'spray'],
                ['name' => 'Digoxin 0.25', 'unit' => 'box-strip'],
                ['name' => 'Amiodarone 200', 'unit' => 'box-strip'],
                ['name' => 'Amiodarone Injection', 'unit' => 'amp'],
                ['name' => 'Warfarin 5', 'unit' => 'box-strip'],
                ['name' => 'Rivaroxaban 10', 'unit' => 'box-strip'],
                ['name' => 'Rivaroxaban 20', 'unit' => 'box-strip'],
                ['name' => 'Apixaban 5', 'unit' => 'box-strip'],
                ['name' => 'Apixaban 2.5', 'unit' => 'box-strip'],
                ['name' => 'Dabigatran 150', 'unit' => 'box-strip'],
                ['name' => 'Clopidogrel 75', 'unit' => 'box-strip'],
                ['name' => 'Ticagrelor 90', 'unit' => 'box-strip'],
                ['name' => 'Aspirin 75', 'unit' => 'box-strip'],
                ['name' => 'Aspirin 100', 'unit' => 'box-strip'],
                ['name' => 'Rosuvastatin 10', 'unit' => 'box-strip'],
                ['name' => 'Rosuvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Atorvastatin 10', 'unit' => 'box-strip'],
                ['name' => 'Atorvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Atorvastatin 40', 'unit' => 'box-strip'],
                ['name' => 'Simvastatin 20', 'unit' => 'box-strip'],
                ['name' => 'Ezetimibe 10', 'unit' => 'box-strip'],
                ['name' => 'Fenofibrate 145', 'unit' => 'box-strip'],
                ['name' => 'Gemfibrozil 600', 'unit' => 'box-strip'],
                ['name' => 'Omeprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Esomeprazole 40', 'unit' => 'box-strip'],
                ['name' => 'Pantoprazole 40', 'unit' => 'box-strip'],
                ['name' => 'Rabeprazole 20', 'unit' => 'box-strip'],
                ['name' => 'Lansoprazole 30', 'unit' => 'box-strip'],
                ['name' => 'Famotidine 20', 'unit' => 'box-strip'],
                ['name' => 'Sucralfate', 'unit' => 'box-strip'],
                ['name' => 'Aluminium Hydroxide', 'unit' => 'bottle'],
                ['name' => 'Magnesium Hydroxide', 'unit' => 'bottle'],
                ['name' => 'Aluminium Magnesium Suspension', 'unit' => 'bottle'],
                ['name' => 'Simethicone', 'unit' => 'bottle'],
                ['name' => 'Gaviscon', 'unit' => 'bottle'],
                ['name' => 'Lactulose', 'unit' => 'bottle'],
                ['name' => 'Bisacodyl', 'unit' => 'box-strip'],
                ['name' => 'Senna', 'unit' => 'box-strip'],
                ['name' => 'Dicyclomine', 'unit' => 'box-strip'],
                ['name' => 'Mebeverine', 'unit' => 'box-strip'],
                ['name' => 'Hyoscine Butylbromide', 'unit' => 'box-strip'],
                ['name' => 'Ondansetron 4', 'unit' => 'box-strip'],
                ['name' => 'Ondansetron 8', 'unit' => 'box-strip'],
                ['name' => 'Ondansetron Injection', 'unit' => 'amp'],
                ['name' => 'Metoclopramide 10', 'unit' => 'box-strip'],
                ['name' => 'Domperidone 10', 'unit' => 'box-strip'],
                ['name' => 'Prochlorperazine', 'unit' => 'box-strip'],
                ['name' => 'Promethazine', 'unit' => 'box-strip'],
                ['name' => 'Dimenhydrinate', 'unit' => 'box-strip'],
                // Part 3 and 4
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
                ['name' => 'Scalix Soap', 'unit' => 'pic'],
                ['name' => 'Scabiderm Soap', 'unit' => 'pic'],
                ['name' => 'Actolind', 'unit' => 'pic'],
                ['name' => 'Diabetic Foot', 'unit' => 'pic'],
                ['name' => 'CD Sitaburn', 'unit' => 'pic'],
                ['name' => 'Honey Care', 'unit' => 'pic'],
                ['name' => 'Sulphagin Cream', 'unit' => 'pic'],
                ['name' => 'Crampnil D', 'unit' => 'pic'],
                ['name' => 'Sito Heal', 'unit' => 'pic'],
                ['name' => 'Magic Cream', 'unit' => 'pic'],
                ['name' => 'Dektazok', 'unit' => 'tab'],
                ['name' => 'Melanthenol', 'unit' => 'tub'],
                ['name' => 'Panthenova B5', 'unit' => 'tub'],
                ['name' => 'Glow Fresh', 'unit' => 'bottle'],
                ['name' => 'Alfacort', 'unit' => 'pic'],
                ['name' => 'Pilax', 'unit' => 'pic'],
                ['name' => 'Proto Heal', 'unit' => 'pic'],
                ['name' => 'Betaderm', 'unit' => 'pic'],
                ['name' => 'Betamet', 'unit' => 'pic'],
                ['name' => 'CD Metazone', 'unit' => 'pic'],
                ['name' => 'Fueidel', 'unit' => 'pic'],
                ['name' => 'Fucine', 'unit' => 'pic'],
                ['name' => 'Fusiderm', 'unit' => 'pic'],
                ['name' => 'CD Tetracin', 'unit' => 'pic'],
                ['name' => 'Supracyline', 'unit' => 'pic'],
                ['name' => 'Teranova', 'unit' => 'pic'],
                ['name' => 'Panaderm', 'unit' => 'pic'],
                ['name' => 'Ketacor', 'unit' => 'tub'],
                ['name' => 'Sicacyline', 'unit' => 'tub'],
                ['name' => 'Exinofin', 'unit' => 'tab'],
                ['name' => 'CD Acnestop', 'unit' => 'tub'],
                ['name' => 'Clovirex Cream', 'unit' => 'tub'],
                ['name' => 'Tetracycline', 'unit' => 'tab'],
                ['name' => 'Candid B', 'unit' => 'tube'],
                ['name' => 'Candid', 'unit' => 'tube'],
                ['name' => 'Cortiderm 1%', 'unit' => 'pic'],
                ['name' => 'Betafucin', 'unit' => 'tab'],
                ['name' => 'Sica Numb', 'unit' => 'tub'],
                ['name' => 'Rutgard Mouth Gel', 'unit' => 'pic'],
                ['name' => 'Retinoid', 'unit' => 'tub'],
                ['name' => 'Supirocin', 'unit' => null],
                ['name' => 'Solache', 'unit' => 'tub'],
                ['name' => 'Melano Massage', 'unit' => 'tub'],
                ['name' => 'Crampsal', 'unit' => 'tub'],
                ['name' => 'Voligesic Plus', 'unit' => 'tub'],
                ['name' => 'Feminova', 'unit' => 'bottle'],
                ['name' => 'Castor Oil', 'unit' => 'bottle'],
                ['name' => 'Peridone Syrup', 'unit' => 'bottle'],
                ['name' => 'Domivent', 'unit' => 'bottle'],
                ['name' => 'Kojic Glow', 'unit' => 'pic'],
                ['name' => 'D Lamis', 'unit' => 'pic'],
                ['name' => 'Ichthamol 10%', 'unit' => 'pic'],
                ['name' => 'Ferpod 100mg', 'unit' => 'box-strip'],
                ['name' => 'Glutavit Soap', 'unit' => 'pic'],
                ['name' => 'Deflat Droporal', 'unit' => 'bottle'],
                ['name' => 'Arnil Gel', 'unit' => 'tub'],
                ['name' => 'Powergel', 'unit' => 'tub'],
                ['name' => 'D max V.D3', 'unit' => 'bottle'],
                ['name' => 'Chipodox 100', 'unit' => 'bottle'],
                ['name' => 'Stopain', 'unit' => 'tub'],
                ['name' => 'Royal Vaseline', 'unit' => 'pic'],
                ['name' => 'Follaxcel Vaseline', 'unit' => 'pic'],
                ['name' => 'Herbal Cough', 'unit' => 'box-strip'],
                ['name' => 'CD Cefcloxime', 'unit' => 'bottle'],
                ['name' => 'Green Zyme', 'unit' => 'bottle'],
                ['name' => 'Orazone Syrup', 'unit' => 'bottle'],
                ['name' => 'Power Cort', 'unit' => 'tube'],
                ['name' => 'Maeva', 'unit' => 'pic'],
                ['name' => 'Adult Pants', 'unit' => 'pic'],
                ['name' => 'Pyredol Syrup', 'unit' => 'bottle'],
                ['name' => 'Lavenola', 'unit' => 'pic'],
                ['name' => 'Waporal Inhalador', 'unit' => null],
                ['name' => 'Purafit Zinc', 'unit' => 'pic'],
                ['name' => 'Flutab', 'unit' => 'box-strip'],
                ['name' => 'Ciprocima 500', 'unit' => 'box-strip'],
                ['name' => 'Fotaz 100 Syrup', 'unit' => 'bottle'],
                ['name' => 'Amixime 100', 'unit' => 'bottle'],
                ['name' => 'CD Lxime 100 Syrup', 'unit' => 'bottle'],
                ['name' => 'Nystasyr', 'unit' => 'bottle'],
                ['name' => 'Fungistatin', 'unit' => 'bottle'],
                ['name' => 'Kamoks', 'unit' => 'bottle'],
                ['name' => 'Amiclav 457', 'unit' => 'bottle'],
                ['name' => 'Augram 228', 'unit' => 'bottle'],
                ['name' => 'Clavimox 228', 'unit' => 'bottle'],
                ['name' => 'Haemill Iron Tonic', 'unit' => 'bottle'],
                ['name' => 'CD Zithro', 'unit' => 'bottle'],
                ['name' => 'Unizithrin', 'unit' => 'bottle'],
                ['name' => 'Aziscot Syrup', 'unit' => 'bottle'],
                ['name' => 'CD Moxillin 250', 'unit' => 'bottle'],
                ['name' => 'Voxfer Drops', 'unit' => 'bottle'],
                /*
                |--------------------------------------------------------------------------
                | Medicines - Part 5
                |--------------------------------------------------------------------------
                */
                ['name' => 'Fenugreek 300mg', 'unit' => 'box'],
                ['name' => 'Bioeast', 'unit' => 'box'],
                ['name' => 'Novitizer', 'unit' => 'pic'],
                ['name' => 'Mixtard 30', 'unit' => 'pic'],
                ['name' => 'Gacet-125 Suppositorise', 'unit' => 'box-supp'],
                ['name' => 'Gacet-250 Suppositorise', 'unit' => 'box-supp'],
                ['name' => 'Diclogesic Suppositorise', 'unit' => 'box'],
                ['name' => 'Glysup Suppositorise', 'unit' => 'box'],
                ['name' => 'Ronkotol Inhalation', 'unit' => 'box-amp'],
                ['name' => 'Caviar', 'unit' => 'box-pic'],
                ['name' => 'Rovista 20mg', 'unit' => 'box'],
                ['name' => 'Bisol 2.5mg', 'unit' => 'box'],
                ['name' => 'Candestan 16mg', 'unit' => 'box-strip'],
                ['name' => 'Diabend', 'unit' => 'box'],
                ['name' => 'Amiprazole 20', 'unit' => 'box'],
                ['name' => 'Trilodin', 'unit' => 'box-strip'],
                ['name' => 'Avenzor Fursemide', 'unit' => 'box'],
                ['name' => 'Allied Salbutomol', 'unit' => 'box'],
                ['name' => 'Tobramax D', 'unit' => 'drop'],
                ['name' => 'Clavenen 228mg', 'unit' => 'bottle'],
                ['name' => 'Fluocinolone', 'unit' => 'pic'],
                ['name' => 'Zecuf Rub', 'unit' => 'pic'],
                ['name' => 'Amitrim 240mg', 'unit' => 'bottle'],
                ['name' => 'Amibutamol Syrup', 'unit' => 'bottle'],
                ['name' => 'Kenazolo Shampo', 'unit' => 'box'],
                ['name' => 'Exizime Forte Oral', 'unit' => 'bottle'],
                ['name' => 'CD Methasalic Lotion', 'unit' => 'pic'],
                ['name' => 'Cento Soap', 'unit' => 'pic'],
                ['name' => 'IV Set', 'unit' => 'set'],
                ['name' => 'Calamine Lotion', 'unit' => 'bottle'],
                ['name' => 'HCA', 'unit' => 'pic'],
                ['name' => 'Right Sign HCG', 'unit' => 'box'],
                ['name' => 'Amiprofen 400', 'unit' => 'strip'],
                ['name' => 'Autest', 'unit' => 'pic'],
                ['name' => 'Prolol 40mg', 'unit' => 'box-strip'],
                ['name' => 'Prolol 10mg', 'unit' => 'box-strip'],
                ['name' => 'Potomax Citrate', 'unit' => 'pic'],
                ['name' => 'سيرلاك القمح والتمر', 'unit' => 'pic'],
                ['name' => 'سيرلاك القمح والفراولة', 'unit' => 'pic'],
                ['name' => 'ضفارة كبيرة', 'unit' => 'pic'],
                ['name' => 'صفارة صغيرة', 'unit' => 'pic'],
                ['name' => 'CD Dibrax', 'unit' => 'box-strip'],
                ['name' => 'Gentasaph Inj', 'unit' => 'box-amp'],
                ['name' => 'Benzathine 2.4', 'unit' => 'vial'],
                ['name' => 'Naglovic 750mg', 'unit' => 'box'],
                ['name' => 'Fusiturli B', 'unit' => 'tub'],
                ['name' => 'ناموسية اطفال', 'unit' => 'pic'],
                ['name' => 'ناموسية ديل', 'unit' => 'pic'],
                ['name' => 'ناموسية فردة', 'unit' => 'pic'],
                ['name' => 'Duphaston 10mg', 'unit' => 'box-strip'],
                ['name' => 'Heli Cure', 'unit' => 'box-strip'],
                ['name' => 'Milga', 'unit' => 'box-strip'],
                ['name' => 'Thyroxine Sodium 50', 'unit' => 'box'],
                ['name' => 'Lipanthy Supra', 'unit' => 'box'],
                ['name' => 'Eucarbon', 'unit' => 'box-strip'],
                ['name' => 'Adramark Inj', 'unit' => 'amp'],
                ['name' => 'Primaquine 15mg', 'unit' => 'strip'],
                ['name' => 'Tegretol 200mg', 'unit' => 'strip'],
                ['name' => 'Amaryl 1mg', 'unit' => 'box-strip'],
                ['name' => 'Dermovate', 'unit' => 'tub'],
                ['name' => 'Fertogard 50', 'unit' => 'box-strip'],
                ['name' => 'Kapron 500 Inj', 'unit' => 'box-amp'],
                ['name' => 'Amaryl 4mg', 'unit' => 'box-strip'],
                ['name' => 'Cold A Go', 'unit' => 'strip-tab'],
                ['name' => 'Inderal 10', 'unit' => 'box'],
                ['name' => 'Diamicron 60mr', 'unit' => 'strip'],
                ['name' => 'Nolvaelex 10', 'unit' => null],
                ['name' => 'Debocaine HCI', 'unit' => 'bottle'],
                ['name' => 'Cinnarizine 75', 'unit' => 'strip'],
                ['name' => 'Hydroxyurea 500', 'unit' => 'strip'],
                ['name' => 'Lamotrine 100', 'unit' => 'box-strip'],
                ['name' => 'Lamotrine 50', 'unit' => 'box-strip'],
                ['name' => 'Dexa Tablet', 'unit' => 'strip'],
                ['name' => 'Vitacide C', 'unit' => 'bottle'],
                ['name' => 'Diazi 5mg Inj', 'unit' => 'ampul'],
                ['name' => 'Valgestril', 'unit' => 'box'],
                ['name' => 'Oxyprogest', 'unit' => 'bottle'],
                ['name' => 'Inderal 40mg', 'unit' => 'box-strip'],
                ['name' => 'Urinex 36', 'unit' => 'box-strip'],
                ['name' => 'Dahab', 'unit' => 'box-tab'],
                ['name' => 'Lasix 20mg/2ml', 'unit' => 'box'],
                ['name' => 'Mepafuran 100', 'unit' => 'box-strip'],
                ['name' => 'AAzithro 500', 'unit' => 'box'],
                ['name' => 'Serpass 100mg', 'unit' => 'box'],
                ['name' => 'Serpass 500mg', 'unit' => 'box'],
                ['name' => 'C Retard 500', 'unit' => 'tab'],
                ['name' => 'Urinex 24', 'unit' => 'box'],
                ['name' => 'Pitem 800', 'unit' => 'strip'],
                ['name' => 'Pain Rest', 'unit' => 'strip'],
                ['name' => 'Praziquantel 600', 'unit' => 'box-tab'],
                ['name' => 'Paindex', 'unit' => 'box-strip'],
                ['name' => 'Rsunate 120', 'unit' => 'vial'],
                ['name' => 'Ramsun 60 Inj', 'unit' => 'vial'],
                ['name' => 'Falcivac 120 Inj', 'unit' => 'vial'],
                ['name' => 'Artedice', 'unit' => null],
                ['name' => 'Ramsun 180 Inj', 'unit' => 'vial'],
                ['name' => 'Artedice 30', 'unit' => 'vial'],
                ['name' => 'Amoxicillin 500', 'unit' => 'strip'],
                ['name' => 'Pantolife 40 Inj', 'unit' => 'vial'],
                ['name' => 'Amri K', 'unit' => 'box-amp'],
                ['name' => 'Lactodel', 'unit' => 'box-strip'],
                ['name' => 'Capotril 50', 'unit' => 'box-strip'],
                ['name' => 'Biomycine Ointm', 'unit' => 'tub'],
                ['name' => 'Mini Guava N', 'unit' => 'drop'],
                ['name' => 'FPI Steadyfatal', 'unit' => 'box-strip'],
                ['name' => 'Silden 50mg', 'unit' => 'box'],
                ['name' => 'Tinika 500', 'unit' => 'strip'],
                ['name' => 'Phen Eptocan', 'unit' => 'amp'],
                ['name' => 'Ateno 100mg', 'unit' => 'box'],
                ['name' => 'Paludose P', 'unit' => 'tab'],
                ['name' => 'Baclofen Lobac', 'unit' => 'strip'],
                ['name' => 'Dapagliflozin 10', 'unit' => 'box'],
                ['name' => 'Verserc 16mg', 'unit' => 'box'],
                ['name' => 'Phenbital', 'unit' => 'strip'],
                ['name' => 'Gyna Mikozal', 'unit' => 'box'],
                ['name' => 'Pantulk Four M', 'unit' => 'pic'],
                ['name' => 'Wellman', 'unit' => 'box-strip'],
                ['name' => 'Hydrocortisone Inj', 'unit' => 'vial'],
                ['name' => 'Top Tulle', 'unit' => 'pic'],
                ['name' => 'Nasal Oxygen Cannullia', 'unit' => 'pic'],
                ['name' => 'Oxygen Mask Pediatric', 'unit' => 'pic'],
                ['name' => 'Nebulizer Mask Adult', 'unit' => 'pic'],
                ['name' => 'Nasal Canullia', 'unit' => 'pic'],
                ['name' => 'Oxygen Mask Adult', 'unit' => 'pic'],
                ['name' => 'Fortymox 0.5%', 'unit' => 'drop'],
                ['name' => 'Hyfresh Drop', 'unit' => 'drop'],
                ['name' => 'Brimonine Drop', 'unit' => 'drop'],
                ['name' => 'Tears Guard Drop', 'unit' => 'drop'],
                ['name' => 'DuoTrav Drop', 'unit' => 'drop'],
                ['name' => 'Plegica Drop', 'unit' => 'drop'],
                ['name' => 'Olohistine Drop', 'unit' => 'drop'],
                ['name' => 'Polyfresh Drop', 'unit' => 'drop'],
                ['name' => 'Epitaxol Drop', 'unit' => 'drop'],
                ['name' => 'Dexatrol Drop', 'unit' => 'drop'],
                ['name' => 'Xolamol Drop', 'unit' => 'drop'],
                ['name' => 'Conjy Chear 0.1%', 'unit' => 'drop'],
                ['name' => 'Fluca Drop', 'unit' => 'drop'],
       
            ];

                    /*
            |--------------------------------------------------------------------------
            | Insert / Update Medicines
            |--------------------------------------------------------------------------
            */

            foreach ($medicines as $medicine) {

                $name = trim($medicine['name']);

                if ($name === '') {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Find existing medicine
                |--------------------------------------------------------------------------
                */

                $medicineId = DB::table('medicines')
                    ->where('name', $name)
                    ->value('id');

                if (!$medicineId) {

                    $medicineId = DB::table('medicines')->insertGetId([
                        'name'              => $name,
                        'scientific_name'   => null,
                        'notes'             => null,
                        'pricing_method'    => 'local',
                        'pricing_rule_id'   => null,
                        'category_id'       => null,

                        /*
                        |--------------------------------------------------------------------------
                        | Legacy packaging fields
                        |--------------------------------------------------------------------------
                        |
                        | The Excel source does not provide conversion factors.
                        |
                        */

                        'strips_per_box'    => 1,
                        'pieces_per_strip'  => 1,

                        'allow_box_sale'    => 1,
                        'allow_strip_sale'  => 1,
                        'allow_piece_sale'  => 1,

                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                } else {

                    DB::table('medicines')
                        ->where('id', $medicineId)
                        ->update([
                            'updated_at' => now(),
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create medicine units
                |--------------------------------------------------------------------------
                */

                $this->createMedicineUnits(
                    $medicineId,
                    $medicine['unit'],
                    $unitMap
                );
            }

            $this->command?->info(
                'MedicinesSeeder completed successfully.'
            );
            });
        }
    

    /*
    |--------------------------------------------------------------------------
    | Create Medicine Units
    |--------------------------------------------------------------------------
    */

    private function createMedicineUnits(
        int $medicineId,
        ?string $definition,
        array $unitMap
    ): void {

        if (!$definition) {
            return;
        }

        $definition = strtolower(trim($definition));

        if ($definition === '') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Split compound definitions
        |--------------------------------------------------------------------------
        |
        | Examples:
        |
        | box-strip
        | box-strip-tab
        | box-pic
        | box-amp
        |
        */

        $parts = explode('-', $definition);

        $sortOrder = 1;

        foreach ($parts as $part) {

            $part = trim($part);

            if ($part === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve source unit to database unit
            |--------------------------------------------------------------------------
            */

            if (!isset($unitMap[$part])) {

                /*
                |--------------------------------------------------------------------------
                | Unknown / intentionally unsupported source unit
                |--------------------------------------------------------------------------
                |
                | Do not create a fake database unit.
                |
                */

                continue;
            }

            $symbol = $unitMap[$part];

            $unit = DB::table('units')
                ->where('symbol', $symbol)
                ->where('active', 1)
                ->first();

            if (!$unit) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Base Unit
            |--------------------------------------------------------------------------
            |
            | The last unit in the source definition is the base unit.
            |
            */

            $isBase = (
                $part === end($parts)
            ) ? 1 : 0;

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate medicine/unit relation
            |--------------------------------------------------------------------------
            */

            $exists = DB::table('medicine_units')
                ->where('medicine_id', $medicineId)
                ->where('unit_id', $unit->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('medicine_units')->insert([

                'medicine_id' => $medicineId,
                'unit_id'     => $unit->id,

                /*
                |--------------------------------------------------------------------------
                | Conversion factor
                |--------------------------------------------------------------------------
                |
                | The Excel source does not contain package conversion factors.
                |
                | Therefore all factors are initially 1.
                |
                */

                'factor'      => 1,

                /*
                |--------------------------------------------------------------------------
                | Barcode
                |--------------------------------------------------------------------------
                |
                | Barcode information is not present in the Excel source.
                |
                */

                'barcode'     => null,

                'is_base'     => $isBase,

                'sort_order'  => $sortOrder,

                'allow_sale'  => 1,

                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $sortOrder++;
        }
    }
}