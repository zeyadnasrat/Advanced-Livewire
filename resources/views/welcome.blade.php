@php use Illuminate\Support\Facades\Blade; @endphp
<html>
    <link href="/app.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/morph@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewire(App\Livewire\Todos::class)

    <script src="/livewire.js"></script>

</html>

