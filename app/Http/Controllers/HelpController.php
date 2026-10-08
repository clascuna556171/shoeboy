<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $guide = config('guides');
        $role = auth()->user()?->isOwner() ? 'owner' : 'staff';

        $generalHelp = $guide['general']['help'] ?? [];
        $intro = collect($generalHelp)->firstWhere('id', 'getting-started');
        $tips = collect($generalHelp)->firstWhere('id', 'tips');

        // Attach the owning page's tour key + URL so each section can offer a
        // "Show me" that runs the right walkthrough on the right page.
        $decorate = function (array $section, string $tourKey, ?string $url): array {
            $section['tourKey'] = $tourKey;
            $section['url'] = $url;

            return $section;
        };

        $sections = array_values(array_filter([$intro ? $decorate($intro, '', null) : null]));

        foreach ($guide['pages'] as $key => $page) {
            if (! empty($page['roles']) && ! in_array($role, $page['roles'], true)) {
                continue;
            }
            $url = ! empty($page['route']) ? route($page['route']) : null;
            foreach ($page['help'] ?? [] as $section) {
                $sections[] = $decorate($section, $key, $url);
            }
        }

        if ($tips) {
            $sections[] = $decorate($tips, '', null);
        }

        return view('help.index', ['sections' => $sections]);
    }
}
