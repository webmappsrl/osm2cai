<x-hikingroute.hikingrouteLayout :hikingroute="$hikingroute">
    <x-hikingroute.hikingrouteHeader :hikingroute="$hikingroute"/>
    <main class="max-w-screen-xl m-auto pb-20">
        <x-mapsection :hikingroute="$hikingroute"/>
        <x-hikingroute.hikingrouteContentSection :hikingroute="$hikingroute" />
    </main>
</x-hikingroute.hikingrouteLayout>