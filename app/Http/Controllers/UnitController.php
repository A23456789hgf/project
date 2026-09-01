<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\ErpNextService;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    use HasApprovalWorkflow;

    private function syncExternalUnits(): void
    {
        Cache::remember('synced_external_units_flag', 600, function () {
            $externalUnits = $this->fetchExternalUnits();
            if (! empty($externalUnits)) {
                $existingMap = array_flip(Unit::pluck('unit_name')->toArray());
                $toInsert = [];
                $now = now();
                foreach ($externalUnits as $extUnit) {
                    $name = $extUnit['uom_name'] ?? ($extUnit['name'] ?? null);
                    if ($name && ! isset($existingMap[$name])) {
                        $toInsert[] = [
                            'unit_name' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $existingMap[$name] = true;
                    }
                }
                if (! empty($toInsert)) {
                    Unit::insert($toInsert);
                }
            }

            return true;
        });
    }

    // عرض كل الوحدات
    public function index(Request $request)
    {
        $this->syncExternalUnits();

        $query = Unit::query();
        $query = $this->applyStatusFilter($query, $request);
        $units = $query->paginate(30)->withQueryString();

        // Add translated name for display
        foreach ($units as $unit) {
            $unit->display_name = $this->translateUnit($unit->unit_name);
        }

        return view('configuration.unit.index', compact('units'));
    }

    public function approve(Unit $unit)
    {
        return $this->approveModel($unit, 'units.index');
    }

    public function reject(Unit $unit)
    {
        return $this->rejectModel($unit, 'units.index');
    }

    // جلب البيانات بصيغة JSON
    public function apiFetch()
    {
        $this->syncExternalUnits();

        $units = Unit::orderBy('unit_name')->get()->map(function ($unit) {
            $translatedName = $this->translateUnit($unit->unit_name);

            return [
                'name' => (string) $unit->id, // Use local ID as primary value
                'uom_name' => $translatedName,
                'unit_name' => $translatedName,
            ];
        })->toArray();

        return response()->json(['data' => $units]);
    }

    // جلب الوحدات حسب البند المالي
    public function byFinancialItem(Request $request)
    {
        $units = Unit::orderBy('unit_name')->get()->map(function ($unit) {
            $translatedName = $this->translateUnit($unit->unit_name);

            return [
                'id' => $unit->id,
                'text' => $translatedName,
            ];
        })->toArray();

        return response()->json(['units' => $units]);
    }

    private function translateUnit($name)
    {
        $translations = [
            'Abampere' => 'أب أمبير',
            'Acre' => 'فدان',
            'Acre (US)' => 'فدان (أمريكي)',
            'Ampere' => 'أمبير',
            'Ampere-Hour' => 'أمبير-ساعة',
            'Ampere-Minute' => 'أمبير-دقيقة',
            'Ampere-Second' => 'أمبير-ثانية',
            'Are' => 'آر',
            'Area' => 'مساحة',
            'Arshin' => 'أرشينا',
            'Atmosphere' => 'ضغط جوي',
            'Bar' => 'بار',
            'Barleycorn' => 'بارلي كورن',
            'Barrel (Oil)' => 'برميل (نفط)',
            'Barrel(Beer)' => 'برميل (بيرة)',
            'Biot' => 'بيوت',
            'Box' => 'صندوق',
            'Btu (It)' => 'وحدة حرارية بريطانية (IT)',
            'Btu (Mean)' => 'وحدة حرارية بريطانية (متوسط)',
            'Btu (Th)' => 'وحدة حرارية بريطانية (TH)',
            'Btu/Hour' => 'وحدة حرارية بريطانية/ساعة',
            'Btu/Minutes' => 'وحدة حرارية بريطانية/دقيقة',
            'Btu/Seconds' => 'وحدة حرارية بريطانية/ثانية',
            'Bushel (UK)' => 'بوشل (بريطاني)',
            'Bushel (US Dry Level)' => 'بوشل (أمريكي جاف)',
            'Caballeria' => 'كاباليريا',
            'Cable Length' => 'طول الكابل',
            'Cable Length (UK)' => 'طول الكابل (بريطاني)',
            'Cable Length (US)' => 'طول الكابل (أمريكي)',
            'Calibre' => 'عيار',
            'Calorie (Food)' => 'سعرة حرارية (غذائية)',
            'Calorie (It)' => 'سعرة حرارية (IT)',
            'Calorie (Mean)' => 'سعرة حرارية (متوسط)',
            'Calorie (Th)' => 'سعرة حرارية (TH)',
            'Calorie/Seconds' => 'سعرة حرارية/ثانية',
            'Carat' => 'قيراط',
            'Celsius' => 'درجة مئوية',
            'Cental' => 'سينتال',
            'Centiarea' => 'سنتيار',
            'Centigram/Litre' => 'سنتيجرام/لتر',
            'Centilitre' => 'سنتيلتر',
            'Centimeter' => 'سنتيمتر',
            'Chain' => 'سلسلة',
            'Coulomb' => 'كولوم',
            'Cubic Centimeter' => 'سنتيمتر مكعب',
            'Cubic Decimeter' => 'ديسيمتر مكعب',
            'Cubic Foot' => 'قدم مكعب',
            'Cubic Inch' => 'بوصة مكعبة',
            'Cubic Meter' => 'متر مكعب',
            'Cubic Millimeter' => 'ملليمتر مكعب',
            'Cubic Yard' => 'ياردة مكعبة',
            'Cup' => 'كوب',
            'Cycle/Second' => 'دورة/ثانية',
            'Day' => 'يوم',
            'Decigram/Litre' => 'ديسيجرام/لتر',
            'Decilitre' => 'ديسيلتر',
            'Decimeter' => 'ديسيمتر',
            'Dekagram/Litre' => 'ديكاجرام/لتر',
            'Dram' => 'درهم',
            'Dyne' => 'داين',
            'Ells (UK)' => 'إل (بريطاني)',
            'Ems(Pica)' => 'إيم (بيكا)',
            'EMU Of Charge' => 'وحدة كهرومغناطيسية للشحنة',
            'EMU of current' => 'وحدة كهرومغناطيسية للتيار',
            'Erg' => 'إرج',
            'Fahrenheit' => 'فهرنهايت',
            'Faraday' => 'فاراداي',
            'Fathom' => 'قامة',
            'Fluid Ounce (UK)' => 'أونصة سائلة (بريطانية)',
            'Fluid Ounce (US)' => 'أونصة سائلة (أمريكية)',
            'Foot' => 'قدم',
            'Foot Of Water' => 'قدم ماء',
            'Foot/Minute' => 'قدم/دقيقة',
            'Foot/Second' => 'قدم/ثانية',
            'Furlong' => 'فرلنغ',
            'Gallon (UK)' => 'جالون (بريطاني)',
            'Gallon Dry (US)' => 'جالون جاف (أمريكي)',
            'Gallon Liquid (US)' => 'جالون سائل (أمريكي)',
            'Gamma' => 'جاما',
            'Gauss' => 'جاوس',
            'Grain' => 'جرين',
            'Grain/Cubic Foot' => 'جرين/قدم مكعب',
            'Grain/Gallon (UK)' => 'جرين/جالون (بريطاني)',
            'Grain/Gallon (US)' => 'جرين/جالون (أمريكي)',
            'Gram' => 'جرام',
            'Gram-Force' => 'جرام-قوة',
            'Gram/Cubic Centimeter' => 'جرام/سنتيمتر مكعب',
            'Gram/Cubic Meter' => 'جرام/متر مكعب',
            'Gram/Cubic Millimeter' => 'جرام/ملليمتر مكعب',
            'Gram/Litre' => 'جرام/لتر',
            'Hand' => 'كف',
            'Hectare' => 'هكتار',
            'Hectogram/Litre' => 'هكتوجرام/لتر',
            'Hectometer' => 'هكتومتر',
            'Hectopascal' => 'هكتوباسكال',
            'Hertz' => 'هيرتز',
            'Horsepower' => 'حصان',
            'Horsepower-Hours' => 'حصان-ساعة',
            'Hour' => 'ساعة',
            'Hundredweight (UK)' => 'قنطار (بريطاني)',
            'Hundredweight (US)' => 'قنطار (أمريكي)',
            'Iches Of Water' => 'بوصة ماء',
            'Inch' => 'بوصة',
            'Inch Pound-Force' => 'بوصة رطل-قوة',
            'Inch/Minute' => 'بوصة/دقيقة',
            'Inch/Second' => 'بوصة/ثانية',
            'Inches Of Mercury' => 'بوصة زئبق',
            'Joule' => 'جول',
            'Joule/Meter' => 'جول/متر',
            'Kelvin' => 'كلفن',
            'Kg' => 'كيلوجرام',
            'Kiloampere' => 'كيلو أمبير',
            'Kilocalorie' => 'كيلو سعرة',
            'Kilocoulomb' => 'كيلو كولوم',
            'Kilogram-Force' => 'كيلوجرام-قوة',
            'Kilogram/Cubic Centimeter' => 'كيلوجرام/سنتيمتر مكعب',
            'Kilogram/Cubic Meter' => 'كيلوجرام/متر مكعب',
            'Kilogram/Litre' => 'كيلوجرام/لتر',
            'Kilohertz' => 'كيلو هيرتز',
            'Kilojoule' => 'كيلو جول',
            'Kilometer' => 'كيلومتر',
            'Kilometer/Hour' => 'كيلومتر/ساعة',
            'Kilopascal' => 'كيلوباسكال',
            'Kilopond' => 'كيلوبوند',
            'Kilopound-Force' => 'كيلو رطل-قوة',
            'Kilowatt' => 'كيلووات',
            'Kilowatt-Hour' => 'كيلووات-ساعة',
            'Kip' => 'كيب',
            'Knot' => 'عقدة',
            'Link' => 'وصلة',
            'Litre' => 'لتر',
            'Litre-Atmosphere' => 'لتر-ضغط جوي',
            'Manzana' => 'مانزانا',
            'Medio Metro' => 'نصف متر',
            'Megacoulomb' => 'ميجا كولوم',
            'Megagram/Litre' => 'ميجا جرام/لتر',
            'Megahertz' => 'ميجا هيرتز',
            'Megajoule' => 'ميجا جول',
            'Megawatt' => 'ميجاواط',
            'Meter' => 'متر',
            'Meter Of Water' => 'متر ماء',
            'Meter/Second' => 'متر/ثانية',
            'Microbar' => 'ميكروبار',
            'Microgram' => 'ميكروجرام',
            'Microgram/Litre' => 'ميكروجرام/لتر',
            'Micrometer' => 'ميكرومتر',
            'Microsecond' => 'ميكروثانية',
            'Mile' => 'ميل',
            'Mile (Nautical)' => 'ميل (بحري)',
            'Mile/Hour' => 'ميل/ساعة',
            'Mile/Minute' => 'ميل/دقيقة',
            'Mile/Second' => 'ميل/ثانية',
            'Milibar' => 'ميلي بار',
            'Milliampere' => 'ميلي أمبير',
            'Millicoulomb' => 'ميلي كولوم',
            'Milligram' => 'ملليجرام',
            'Milligram/Cubic Centimeter' => 'ملليجرام/سنتيمتر مكعب',
            'Milligram/Cubic Meter' => 'ملليجرام/متر مكعب',
            'Milligram/Cubic Millimeter' => 'ملليجرام/ملليمتر مكعب',
            'Milligram/Litre' => 'ملليجرام/لتر',
            'Millihertz' => 'ميليهيرتز',
            'Millilitre' => 'ملليلتر',
            'Millimeter' => 'ملليمتر',
            'Millimeter Of Mercury' => 'ملليمتر زئبق',
            'Millimeter Of Water' => 'ملليمتر ماء',
            'Millisecond' => 'مللي ثانية',
            'Minute' => 'دقيقة',
            'Nanocoulomb' => 'نانو كولوم',
            'Nanogram/Litre' => 'نانوجرام/لتر',
            'Nanohertz' => 'نانوهيرتز',
            'Nanometer' => 'نانومتر',
            'Nanosecond' => 'نانوثانية',
            'Newton' => 'نيوتن',
            'Nos' => 'عدد',
            'Number' => 'رقم',
            'Ounce' => 'أونصة',
            'Ounce-Force' => 'أونصة-قوة',
            'Ounce/Cubic Foot' => 'أونصة/قدم مكعب',
            'Ounce/Cubic Inch' => 'أونصة/بوصة مكعبة',
            'Ounce/Gallon (UK)' => 'أونصة/جالون (بريطاني)',
            'Ounce/Gallon (US)' => 'أونصة/جالون (أمريكي)',
            'Pair' => 'زوج',
            'Parts Per Million' => 'جزء في المليون',
            'Pascal' => 'باسكال',
            'Peck' => 'بيك',
            'Percent' => 'بالمائة',
            'Pint (UK)' => 'باينت (بريطاني)',
            'Pint, Dry (US)' => 'باينت جاف (أمريكي)',
            'Pint, Liquid (US)' => 'باينت سائل (أمريكي)',
            'Pond' => 'بوند',
            'Pood' => 'بود',
            'Pound' => 'رطل',
            'Pound-Force' => 'رطل-قوة',
            'Pound/Cubic Foot' => 'رطل/قدم مكعب',
            'Pound/Cubic Inch' => 'رطل/بوصة مكعبة',
            'Pound/Cubic Yard' => 'رطل/ياردة مكعبة',
            'Pound/Gallon (UK)' => 'رطل/جالون (بريطاني)',
            'Pound/Gallon (US)' => 'رطل/جالون (أمريكي)',
            'Poundal' => 'باوندال',
            'Psi/1000 Feet' => 'رطل لكل بوصة مربعة/1000 قدم',
            'Quart (UK)' => 'كوارت (بريطاني)',
            'Quart Dry (US)' => 'كوارت جاف (أمريكي)',
            'Quart Liquid (US)' => 'كوارت سائل (أمريكي)',
            'Quintal' => 'قنطار',
            'Rod' => 'رود',
            'Sazhen' => 'ساجين',
            'Second' => 'ثانية',
            'Set' => 'مجموعة',
            'Slug' => 'سبيكة',
            'Slug/Cubic Foot' => 'سبيكة/قدم مكعب',
            'Square Centimeter' => 'سنتيمتر مربع',
            'Square Foot' => 'قدم مربع',
            'Square Inch' => 'بوصة مربعة',
            'Square Kilometer' => 'كيلومتر مربع',
            'Square Meter' => 'متر مربع',
            'Square Mile' => 'ميل مربع',
            'Square Yard' => 'ياردة مربعة',
            'Stone' => 'ستون',
            'Tablespoon (US)' => 'ملعقة كبيرة (أمريكي)',
            'Teaspoon' => 'ملعقة صغيرة',
            'Technical Atmosphere' => 'ضغط جوي تقني',
            'Tesla' => 'تسلا',
            'Ton (Long)/Cubic Yard' => 'طن (طويل)/ياردة مكعبة',
            'Ton (Short)/Cubic Yard' => 'طن (قصير)/ياردة مكعبة',
            'Ton-Force (UK)' => 'طن-قوة (بريطاني)',
            'Ton-Force (US)' => 'طن-قوة (أمريكي)',
            'Tonne' => 'طن',
            'Tonne-Force(Metric)' => 'طن-قوة (متري)',
            'Torr' => 'تور',
            'Unit' => 'وحدة',
            'Vara' => 'فارا',
            'Versta' => 'فيرستا',
            'Volt-Ampere' => 'فولت-أمبير',
            'Watt' => 'واط',
            'Watt-Hour' => 'واط-ساعة',
            'Wavelength In Gigametres' => 'طول موجي بالجيجامتر',
            'Wavelength In Kilometres' => 'طول موجي بالكيلومتر',
            'Wavelength In Megametres' => 'طول موجي بالميجامتر',
            'Week' => 'أسبوع',
            'Yard' => 'ياردة',
            'Dozen' => 'درزن',
            'Piece' => 'قطعة',
            'Each' => 'لكل',
            'Carton' => 'كرتون',
            'Package' => 'حزمة',
            'Bag' => 'كيس',
            'Bottle' => 'زجاجة',
            'Month' => 'شهر',
            'Year' => 'سنة',
        ];

        return $translations[$name] ?? $name;
    }

    private function fetchExternalUnits()
    {
        try {
            $erp = new ErpNextService;

            $response = $erp->get('/api/resource/UOM', [
                'fields' => json_encode(['name', 'uom_name']),
                'limit_page_length' => 500,
            ]);

            if (! $response->successful()) {
                Log::error('UOM API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            return $response->json('data') ?? [];

        } catch (\Exception $e) {
            Log::error('UOM API Exception: '.$e->getMessage());

            return [];
        }
    }

    // =====================================
    // دوال CRUD العادية
    // =====================================
    public function create()
    {
        return view('configuration.unit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255|unique:units,unit_name',
        ], [
            'unit_name.required' => 'اسم الوحدة مطلوب',
            'unit_name.unique' => 'اسم الوحدة موجود مسبقاً',
            'unit_name.max' => 'اسم الوحدة يجب ألا يتجاوز 255 حرف',
        ]);

        try {
            DB::beginTransaction();

            Unit::create([
                'unit_name' => $request->unit_name,
            ]);

            DB::commit();

            return redirect()->route('units.index')
                ->with('success', 'تم إضافة الوحدة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء إضافة الوحدة: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);

        return view('configuration.unit.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255|unique:units,unit_name,'.$id,
        ], [
            'unit_name.required' => 'اسم الوحدة مطلوب',
            'unit_name.unique' => 'اسم الوحدة موجود مسبقاً',
            'unit_name.max' => 'اسم الوحدة يجب ألا يتجاوز 255 حرف',
        ]);

        try {
            DB::beginTransaction();

            $unit = Unit::findOrFail($id);
            $unit->update([
                'unit_name' => $request->unit_name,
            ]);

            DB::commit();

            return redirect()->route('units.index')
                ->with('success', 'تم تحديث الوحدة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء تحديث الوحدة: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $unit = Unit::findOrFail($id);
            $unit->delete();

            DB::commit();

            return redirect()->route('units.index')
                ->with('success', 'تم حذف الوحدة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'حدث خطأ أثناء حذف الوحدة: '.$e->getMessage());
        }
    }
}
