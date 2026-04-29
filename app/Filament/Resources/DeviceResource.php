<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static UnitEnum|string|null $navigationGroup = 'Main';

    public static function form(Schema $schema): Schema
    {
        // Devices are read-only and created by the Capacitor client hitting the API
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_identifier')
                    ->label('Device UUID')
                    ->searchable()
                    ->copyable()
                    ->limit(12),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ios' => 'gray',
                        'android' => 'success',
                        'web' => 'info',
                        default => 'primary',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('bundle.application.name')
                    ->label('Application')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latestLog.ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(fn (Device $record) => ($record->latestLog?->city && $record->latestLog?->country) ? "{$record->latestLog->city}, {$record->latestLog->country}" : 'Unknown')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('os_version')
                    ->label('OS / Model')
                    ->getStateUsing(fn (Device $record) => trim(($record->latestLog?->os_version ?? '').' '.($record->latestLog?->device_model ?? '')) ?: 'Unknown')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_active_at')
                    ->dateTime()
                    ->sortable()
                    ->description(fn (Device $record) => $record->last_active_at?->diffForHumans()),
                Tables\Columns\TextColumn::make('bundle.name')
                    ->label('Current Bundle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('application')
                    ->label('Application')
                    ->relationship(
                        'bundle.application',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('user_id', auth()->id())
                    )
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Channel')
                    ->relationship('bundle.channel', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'ios' => 'iOS',
                        'android' => 'Android',
                        'web' => 'Web',
                    ]),
                Tables\Filters\SelectFilter::make('bundle_id')
                    ->label('Bundle')
                    ->relationship('bundle', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()?->is_admin) {
            return $query;
        }

        return $query->whereHas('bundle.application', fn (Builder $query) => $query->where('user_id', Auth::id()))->orderBy('last_active_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDevices::route('/'),
        ];
    }
}
