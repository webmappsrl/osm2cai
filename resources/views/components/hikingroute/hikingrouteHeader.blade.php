@props(['hikingroute'])


<header>
    <div class="grid grid-cols-2 py-4 px-4 sm:px-20">
        <!-- Webmapp logo section -->
        <div class="col-span-1">
            <img src="" alt="webmapp logo" class="">
        </div>
    </div>
    <div class="mx-auto bg-cover bg-center bg-no-repeat" style="">
        <div class="h-80 sm:h-96 grid grid-cols-3 grid-rows-6 transparent-overlay">
            <!-- Empty section for orginizing -->
            <div class="row-span-2 col-span-full sm:row-span-3 py-6 px-4 sm:px-20">
            </div>
            <!-- Download desktop section -->
            <div class="row-span-4 col-span-full sm:col-start-3 sm:col-end-4 sm:row-start-4 sm:row-end-7 py-4 px-4 sm:max-w-sm">
                <div class="bg-white bg-opacity-70 rounded-lg max-w-md h-full flex flex-col justify-center gap-y-4 px-6">
                    <div class="flex gap-x-6 justify-left items-center">
                        <div><img src="" width="50"  alt="app name"></div>
                        <p class="font-semibold text-xl">{{ __("Scarica l'APP!") }}</p>
                    </div>
                    <div class="flex w-full justify-between">
                        
                    </div>
                </div>
            </div>

            <!-- Title section -->
            @if ($hikingroute->name)
            <div class="text-white col-span-full text-2xl sm:text-3xl font-semibold px-4 sm:px-6 lg:px-20 sm:col-span-2 flex items-end">
                <h1>{!! $hikingroute->name !!}</h1>
            </div>
            @endif

        </div>
        
    </div>
</header>