<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiTokenResource\Pages;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class ApiTokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;

    protected static ?string $modelLabel = 'API Token';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-key';

    protected static UnitEnum|string|null $navigationGroup = 'Main';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('never_expires')
                    ->label('Never Expires')
                    ->default(true)
                    ->reactive()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                DateTimePicker::make('expires_at')
                    ->hidden(fn (callable $get) => $get('never_expires'))
                    ->minDate(now()->addMinute())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('plain_text_token')
                    ->label('Token String')
                    ->copyable()
                    ->copyMessage('Token copied')
                    ->copyMessageDuration(1500)
                    ->fontFamily('mono')
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ? str($state)->limit(15) . '...' : 'Unknown'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->description(fn (PersonalAccessToken $record) => $record->created_at->diffForHumans()),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('expired')
                    ->placeholder('All tokens')
                    ->trueLabel('Expired tokens')
                    ->falseLabel('Active tokens')
                    ->queries(
                        true: fn (Builder $query) => $query->where('expires_at', '<', now()),
                        false: fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now())),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Action::make('copy')
                    ->icon('heroicon-o-clipboard-document')
                    ->label('Copy')
                    ->color('gray')
                    ->url('#')
                    ->extraAttributes(fn ($record) => [
                        'x-on:click.prevent' => "window.navigator.clipboard.writeText('{$record->plain_text_token}'); \$tooltip('Copied to clipboard');",
                    ]),
                \Filament\Actions\DeleteAction::make()
                    ->label('Revoke')
                    ->modalHeading('Revoke Token'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Revoke Selected'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tokenable_id', Auth::id())
            ->where('tokenable_type', \App\Models\User::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageApiTokens::route('/'),
        ];
    }
}
