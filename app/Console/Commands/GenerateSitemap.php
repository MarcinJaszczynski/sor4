<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\BlogPost;
use App\Models\EventTemplate;
use App\Models\Place;
use App\Models\EventTemplateStartingPlaceAvailability;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sitemap generation...');
        $sitemap = Sitemap::create();

        // 1. Strony globalne
        $sitemap->add(Url::create(route('blog.global'))->setPriority(0.8));
        $sitemap->add(Url::create(route('documents.global'))->setPriority(0.5));

        // 2. Blog Posts
        $this->info('Processing Blog Posts...');
        BlogPost::where('is_published', true)->get()->each(function (BlogPost $post) use ($sitemap) {
            $sitemap->add(Url::create(route('blog.post.global', $post->slug))->setPriority(0.7));
        });

        // 3. Regions (Active Start Places)
        $this->info('Processing Regions...');
        $placeIds = EventTemplateStartingPlaceAvailability::select('start_place_id')->distinct()->pluck('start_place_id');
        $regions = Place::whereIn('id', $placeIds)->get();

        // Fallback: If no availability, at least add Warszawa if exists (default region)
        if ($regions->isEmpty()) {
            $warszawa = Place::where('slug', 'warszawa')->orWhere('name', 'Warszawa')->first();
            if ($warszawa) {
                $regions->push($warszawa);
            }
        }

        foreach ($regions as $region) {
            $slug = $region->slug;
            if (!$slug) continue; // Skip if no slug

            // Region static pages
            $sitemap->add(Url::create(route('home', ['regionSlug' => $slug]))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
            $sitemap->add(Url::create(route('packages', ['regionSlug' => $slug]))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
            $sitemap->add(Url::create(route('contact', ['regionSlug' => $slug]))->setPriority(0.5));
            $sitemap->add(Url::create(route('insurance', ['regionSlug' => $slug]))->setPriority(0.5));

            // Offers (EventTemplates) available in this region
            $templateIds = EventTemplateStartingPlaceAvailability::where('start_place_id', $region->id)->pluck('event_template_id');
            $templates = EventTemplate::whereIn('id', $templateIds)
                ->where('is_active', true)
                ->get();

            foreach ($templates as $template) {
                if (!$template->slug) continue;

                 $dayLength = ($template->duration_days ?? 1) . '-dniowe';
                 
                 // Using 'package.pretty': /{regionSlug}/{dayLength}/{id}/{slug}
                 $url = route('package.pretty', [
                     'regionSlug' => $slug,
                     'dayLength' => $dayLength,
                     'id' => $template->id,
                     'slug' => $template->slug
                 ]);
                 
                 $sitemap->add(Url::create($url)->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
            }
        }

        // Write to file
        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info("Sitemap generated successfully at: {$path}");
    }
}
