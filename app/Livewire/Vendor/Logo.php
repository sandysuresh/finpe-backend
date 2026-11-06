<?php

namespace App\Livewire\Vendor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Logo extends Component
{
    use WithFileUploads;

    public $logo = null;

    public string $successMsg = '';

    public function save(): void
    {
        $this->successMsg = '';
        $this->validate([
            'logo' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048',
                'dimensions:min_width=32,min_height=32,max_width=4000,max_height=4000',
            ],
        ]);

        $vendor = Auth::guard('vendor')->user();
        if (! $this->logo instanceof TemporaryUploadedFile) {
            $this->addError('logo', 'Upload a PNG, JPG, JPEG, or WebP logo.');

            return;
        }

        $extension = match ($this->logo->getMimeType()) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = 'vendor-logos/'.$vendor->id;
        $path = $this->logo->storeAs($directory, $filename, 'public');
        $this->deleteStoredLogo($vendor->logo_path, (int) $vendor->id);
        $vendor->forceFill(['logo_path' => $path])->save();
        $this->logo = null;
        $this->successMsg = 'Logo updated.';
        $this->redirect(route('vendor.logo'), navigate: false);
    }

    public function remove(): void
    {
        $vendor = Auth::guard('vendor')->user();
        $this->deleteStoredLogo($vendor->logo_path, (int) $vendor->id);
        $vendor->forceFill(['logo_path' => null])->save();
        $this->successMsg = 'Logo removed.';
        $this->redirect(route('vendor.logo'), navigate: false);
    }

    public function render()
    {
        return view('livewire.vendor.logo', [
            'currentLogo' => Auth::guard('vendor')->user()->logo_path,
        ])->layout('layouts.vendor', ['title' => 'Upload Logo']);
    }

    private function deleteStoredLogo(?string $path, int $vendorId): void
    {
        if (! is_string($path) || ! str_starts_with($path, 'vendor-logos/'.$vendorId.'/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
