<?php

namespace App\Providers\Filament;

use Edwink\FilamentUserActivity\FilamentUserActivityPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->brandName('Fundação Brilhante')
            ->path('admin')
            ->font('Poppins')
            ->brandLogo(asset('fb.png'))
            ->brandLogoHeight(fn () => auth()->check() ? '2.5rem' : '3.3rem')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => auth()->check() ? auth()->user()->name : 'Guest')
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \Edwink\FilamentUserActivity\Http\Middleware\RecordUserActivity::class,
            ])
            ->plugin(
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('Meu Perfil')
                    ->setNavigationLabel('Meu Perfil')
                    ->setNavigationGroup('Meu Perfil')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    // ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    //->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    )
            )

            ->globalSearchKeyBindings(['command+l', 'ctrl+l'])
            ->globalSearchFieldKeyBindingSuffix()
            ->plugins([
                ActivitylogPlugin::make()
                    ->navigationGroup('Auditoria')
                    ->label('Log de Actividade')
                    ->pluralLabel('Log de Actividades')
                    ->navigationSort(-10)
                    ->navigationIcon('heroicon-o-shield-check')
                    ->navigationCountBadge(true)
                    ->authorize(
                        fn() => auth()->check() ? auth()->user()->hasRole('Admin') : false
                    ),

                FilamentProgressbarPlugin::make()
                    ->color('#4dbd1f'),

                SpotlightPlugin::make(),

                // EnvironmentIndicatorPlugin::make()
                //     ->visible(fn () => auth()->check() ? auth()->user()->hasRole('Admin') : false),
            ])
            ->plugin(
                FilamentUserActivityPlugin::make()
            )


            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
        ;
    }
}
