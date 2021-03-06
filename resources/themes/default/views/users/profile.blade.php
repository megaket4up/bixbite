<section class="profile_page">
    <div class="profile_page__inner">
        <header class="profile_page__header">
            <div class="profile_page__picture" style="background-image:url('{{ theme('images/cover.jpg') }}')">
                <div class="profile_page__picture_overlay"></div>
            </div>
            <div class="profile_page__box_layout">
                <figure class="profile_page__avatar_wrapper">
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" width="64" class="profile_page__avatar" />
                </figure>
                <div class="profile_page__name_wrapper">
                    <div class="profile_page__name">{{ $user->name }} <b class="{{ $user->isOnline() ? 'is_online' : '' }}"></b></div>
                    <div class="profile_page__role">@lang('users.role.'.$user->role)</div>
                </div>

                <div class="profile_page__action">
                    @if (user('id') == $user->id)
                        {{-- Если это профиль текущего пользователя. --}}
                        <a href="{{ route('profile.edit', $user) }}" class="btn btn-outline-primary">@lang('users.btn.profile.edit')</a>
                    @elseif (user())
                        {{-- Если профиль просматривает зарегистрированный пользватель. --}}
                        <div class="dropdown">
                            <a href="#" data-toggle="dropdown" class="btn btn-outline-primary dropdown-toggle">Following</a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="{{ route('follow', $user) }}" class="dropdown-item">Follow</a>
                                <a href="#" class="dropdown-item">Private message</a>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('unfollow', $user) }}" class="dropdown-item">Unfollow</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <ul class="profile_page__tabs nav" role="tablist">
            <li class="profile_page_tabs__item">
                <a href="#wall" class="profile_page_tabs__link active" data-toggle="tab" role="tab">Wall</a>
            </li>
            <li class="profile_page_tabs__item">
                <a href="#profile" class="profile_page_tabs__link" data-toggle="tab" role="tab">Profile</a>
            </li>
            <li class="profile_page_tabs__item">
                <a href="#follows" class="profile_page_tabs__link" data-toggle="tab" role="tab">Following</a>
            </li>
            <li class="profile_page_tabs__item">
                <a href="{{ route('articles.index', ['user_id' => $user->id])}}" class="profile_page_tabs__link" role="tab">Articles</a>
            </li>
            {{-- <li class="profile_page_tabs__item">
                <a href="#media" class="profile_page_tabs__link" data-toggle="tab" role="tab">Media</a>
            </li> --}}
        </ul>
        <div class="tab-content">
            <div id="wall" class="tab-pane fade show active" role="tabpanel">@include('users.partials.wall')</div>
            <div id="profile" class="tab-pane fade" role="tabpanel">@include('users.partials.profile')</div>
            <div id="follows" class="tab-pane fade" role="tabpanel" aria-labelledby="contact-tab">@include('users.partials.follows')</div>
            {{-- <div id="media" class="tab-pane fade" role="tabpanel" aria-labelledby="contact-tab">@include('users.partials.media')</div> --}}
        </div>
    </div>
</section>
