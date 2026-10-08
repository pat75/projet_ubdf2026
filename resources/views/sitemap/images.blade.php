<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($images as $image)
    <url>
        <loc>{{ $image['loc'] }}</loc>
@isset($image['date'])
        <lastmod>{{ $image['date'] }}</lastmod>
@endisset
        <image:image><image:loc>{{ $image['image'] }}</image:loc></image:image>
    </url>
@endforeach
</urlset>
