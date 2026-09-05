$controllers = Get-ChildItem -Path "app/Http/Controllers/Admin" -Filter "*.php" -File

foreach ($file in $controllers) {
    $content = Get-Content -Path $file.FullName -Raw
    
    # Update namespace
    $content = $content -replace 'namespace App\\Http\\Controllers;', 'namespace App\Http\Controllers\Admin;'
    
    # Ensure 'use App\Http\Controllers\Controller;' is added if not there
    if ($content -notmatch 'use App\\Http\\Controllers\\Controller;') {
        $content = $content -replace 'namespace App\\Http\\Controllers\\Admin;', "namespace App\Http\Controllers\Admin;`r`n`r`nuse App\Http\Controllers\Controller;"
    }
    
    # Update Request imports
    $content = $content -replace 'use App\\Http\\Requests\\', 'use App\Http\Requests\Admin\'
    
    # Update view paths
    $content = $content -replace "view\('categories\.", "view('admin.categories."
    $content = $content -replace "view\('products\.", "view('admin.products."
    $content = $content -replace "view\('colors\.", "view('admin.colors."
    $content = $content -replace "view\('sizes\.", "view('admin.sizes."
    
    # Update redirect routes
    $content = $content -replace "route\('categories\.", "route('admin.categories."
    $content = $content -replace "route\('products\.", "route('admin.products."
    $content = $content -replace "route\('colors\.", "route('admin.colors."
    $content = $content -replace "route\('sizes\.", "route('admin.sizes."
    $content = $content -replace "route\('product_images\.", "route('admin.product_images."
    
    Set-Content -Path $file.FullName -Value $content
}

$requests = Get-ChildItem -Path "app/Http/Requests/Admin" -Filter "*.php" -File
foreach ($file in $requests) {
    $content = Get-Content -Path $file.FullName -Raw
    $content = $content -replace 'namespace App\\Http\\Requests;', 'namespace App\Http\Requests\Admin;'
    Set-Content -Path $file.FullName -Value $content
}

$views = Get-ChildItem -Path "resources/views/admin" -Filter "*.blade.php" -Recurse -File
foreach ($file in $views) {
    $content = Get-Content -Path $file.FullName -Raw
    $content = $content -replace "@extends\('layouts\.admin'\)", "@extends('admin.layouts.admin')"
    $content = $content -replace "@extends\('layouts\.app'\)", "@extends('admin.layouts.app')"
    
    $content = $content -replace "route\('categories\.", "route('admin.categories."
    $content = $content -replace "route\('products\.", "route('admin.products."
    $content = $content -replace "route\('colors\.", "route('admin.colors."
    $content = $content -replace "route\('sizes\.", "route('admin.sizes."
    $content = $content -replace "route\('product_images\.", "route('admin.product_images."
    
    Set-Content -Path $file.FullName -Value $content
}
