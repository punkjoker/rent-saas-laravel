<?php

namespace App\Http\Controllers;

use App\Models\VacancyListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AdminVacancyController extends Controller
{
    public function index()
    {
        $vacancies = VacancyListing::where('source', 'admin')
            ->with('images')
            ->latest()
            ->paginate(15);

        return view('admin.vacancies.index', compact('vacancies'));
    }

    public function create()
    {
        return view('admin.vacancies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $paths = [];

        try {
            DB::transaction(function () use ($validated, $request, &$paths) {
                $listing = VacancyListing::create([
                    'source' => 'admin',
                    'created_by' => $request->user()->id,
                    'title' => $validated['title'],
                    'place_name' => $validated['place_name'],
                    'address' => $validated['address'] ?? null,
                    'unit_type' => $validated['unit_type'] ?? null,
                    'monthly_rent' => $validated['monthly_rent'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                $this->storeImages($listing, $request, $paths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return redirect()->route('admin.vacancies')
            ->with('success', 'Vacancy published on the public homepage.');
    }

    public function edit(VacancyListing $vacancy)
    {
        $this->assertAdminVacancy($vacancy);
        $vacancy->load('images');

        return view('admin.vacancies.edit', compact('vacancy'));
    }

    public function update(Request $request, VacancyListing $vacancy)
    {
        $this->assertAdminVacancy($vacancy);
        $validated = $request->validate($this->rules());
        $paths = [];

        try {
            DB::transaction(function () use ($vacancy, $validated, $request, &$paths) {
                $locked = VacancyListing::whereKey($vacancy->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->images()->count() + count($request->file('images', [])) > 8) {
                    throw ValidationException::withMessages([
                        'images' => 'A vacancy can have no more than eight photos.',
                    ]);
                }

                $locked->update([
                    'title' => $validated['title'],
                    'place_name' => $validated['place_name'],
                    'address' => $validated['address'] ?? null,
                    'unit_type' => $validated['unit_type'] ?? null,
                    'monthly_rent' => $validated['monthly_rent'],
                    'bedrooms' => $validated['bedrooms'] ?? null,
                    'bathrooms' => $validated['bathrooms'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'description' => $validated['description'] ?? null,
                ]);
                $this->storeImages($locked, $request, $paths);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);
            throw $exception;
        }

        return redirect()->route('admin.vacancies')
            ->with('success', 'Vacancy details updated.');
    }

    public function toggle(VacancyListing $vacancy)
    {
        $this->assertAdminVacancy($vacancy);
        $vacancy->update([
            'status' => $vacancy->status === 'published' ? 'unpublished' : 'published',
            'published_at' => $vacancy->status === 'published' ? null : now(),
        ]);

        return back()->with('success', 'Vacancy publication status updated.');
    }

    public function destroy(VacancyListing $vacancy)
    {
        $this->assertAdminVacancy($vacancy);
        $paths = $vacancy->images->pluck('image_path')->all();
        DB::transaction(fn () => $vacancy->delete());
        Storage::disk('public')->delete($paths);

        return redirect()->route('admin.vacancies')
            ->with('success', 'Vacancy advertisement deleted.');
    }

    public function destroyImage(VacancyListing $vacancy, int $image)
    {
        $this->assertAdminVacancy($vacancy);
        $record = $vacancy->images()->findOrFail($image);
        abort_unless(
            Storage::disk('public')->delete($record->image_path),
            500,
            'Could not remove the vacancy photo.'
        );
        $record->delete();

        return back()->with('success', 'Vacancy photo removed.');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'place_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:80'],
            'monthly_rent' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'bedrooms' => ['nullable', 'integer', 'between:0,50'],
            'bathrooms' => ['nullable', 'integer', 'between:0,50'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    private function assertAdminVacancy(VacancyListing $vacancy): void
    {
        abort_unless($vacancy->source === 'admin', 404);
    }

    private function storeImages(
        VacancyListing $listing,
        Request $request,
        array &$paths
    ): void {
        $sortOrder = (int) $listing->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $image) {
            $path = $image->storePublicly('vacancies', 'public');
            if (! $path) {
                throw new RuntimeException('Failed to store a vacancy photo.');
            }

            $paths[] = $path;
            $listing->images()->create([
                'image_path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
