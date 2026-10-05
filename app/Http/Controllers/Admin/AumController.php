<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SavesWebpImages;
use App\Http\Controllers\Controller;
use App\Models\Aum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AumController extends Controller
{
    use SavesWebpImages;

    public function index(Request $request): Response
    {
        $type = $request->query('jenis', 'semua');
        $search = $request->query('cari');

        $query = Aum::query()->ordered();

        if ($type && $type !== 'semua' && array_key_exists($type, Aum::TYPES)) {
            $query->where('type', $type);
        }

        if ($search) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('category', 'like', "%{$escaped}%")
                    ->orWhere('leader', 'like', "%{$escaped}%")
                    ->orWhere('address', 'like', "%{$escaped}%");
            });
        }

        $counts = Aum::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return Inertia::render('Admin/Aums/Index', [
            'items' => $query->get(),
            'types' => Aum::TYPES,
            'counts' => $counts,
            'categorySuggestions' => Aum::query()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'filters' => [
                'jenis' => $type ?: 'semua',
                'cari' => $search ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->storeImageAsWebp($request->file('image'), 'images/aum', 1000, $validated['name']);
        }
        unset($validated['image'], $validated['remove_image']);

        $validated['sort_order'] ??= (int) Aum::where('type', $validated['type'])->max('sort_order') + 1;

        Aum::create($validated);

        $label = Aum::TYPES[$validated['type']];

        return back()->with('success', "Data {$label} berhasil ditambahkan!");
    }

    public function update(Request $request, Aum $aum): RedirectResponse
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image')) {
            $this->deleteLocalImage($aum->image_url);
            $validated['image_url'] = $this->storeImageAsWebp($request->file('image'), 'images/aum', 1000, $validated['name']);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteLocalImage($aum->image_url);
            $validated['image_url'] = null;
        } elseif (! empty($validated['image_url']) && $validated['image_url'] !== $aum->image_url) {
            $this->deleteLocalImage($aum->image_url);
        } else {
            // Pertahankan gambar lama bila tidak ada perubahan
            unset($validated['image_url']);
        }
        unset($validated['image'], $validated['remove_image']);

        $validated['sort_order'] ??= $aum->sort_order;

        $aum->update($validated);

        return back()->with('success', 'Data berhasil diperbarui!');
    }

    public function toggleActive(Aum $aum): RedirectResponse
    {
        $aum->update(['is_active' => ! $aum->is_active]);

        return back()->with('success', $aum->is_active
            ? "\"{$aum->name}\" kini ditampilkan di halaman publik."
            : "\"{$aum->name}\" disembunyikan dari halaman publik.");
    }

    public function destroy(Aum $aum): RedirectResponse
    {
        $this->deleteLocalImage($aum->image_url);
        $aum->delete();

        return back()->with('success', 'Data berhasil dihapus!');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(array_keys(Aum::TYPES))],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^mdi-[a-z0-9-]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'leader' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:1000'],
            'remove_image' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ], [
            'icon.regex' => 'Format ikon harus berupa nama Material Design Icon, contoh: mdi-school.',
        ]);
    }
}
