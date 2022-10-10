<dropdown-trigger class="h-9 flex items-center">
    <?php
    $emulatedUserId = intval(session('emulate_user_id'));
    $emulatedUser = $emulatedUserId > 0
        ? \App\Models\User::find($emulatedUserId)
        : $user;
    ?>

    @isset($emulatedUser->email)
        <img
                src="https://secure.gravatar.com/avatar/{{ md5(\Illuminate\Support\Str::lower($emulatedUser->email)) }}?size=512"
                class="rounded-full w-8 h-8 mr-3"
        />
    @endisset

    <span class="text-90">
        {{ $emulatedUser->name ?? $emulatedUser->email ?? __('Nova User') }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="200" direction="rtl">
    <ul class="list-reset">
        @if ($user->is_administrator > 0)
            <li>
                <router-link :to="{
                    name: 'detail',
                    params: {
                        resourceName: 'users',
                        resourceId: '{{ $emulatedUserId > 0 ? $emulatedUserId : $user->id }}'
                    }
                }" class="block no-underline text-90 hover:bg-30 p-3"
                            id="wm-user-profile-button">
                    {{ __('Profile') }}
                </router-link>
            </li>
        @endif
        @if ($emulatedUserId > 0)
            <li>
                <a href="{{ route('emulatedUser.restore') }}" class="block no-underline text-90 hover:bg-30 p-3">
                    {{ __('Restore User') }}
                </a>
            </li>
        @else
            <li>
                <a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3"
                   id="wm-user-logout-button">
                    {{ __('Logout') }}
                </a>
            </li>
        @endif
    </ul>
</dropdown-menu>
