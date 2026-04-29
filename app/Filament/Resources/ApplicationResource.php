<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static UnitEnum|string|null $navigationGroup = 'Main';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('uuid')
                            ->label('Application ID')
                            ->copyable(true)
                            ->copyMessage('Application ID copied')
                            ->copyMessageDuration(1500),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Application Name'),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('URL Slug'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->markdown(),

                        Infolists\Components\RepeatableEntry::make('bundles')
                            ->label('Bundles')
                            ->schema([
                                Infolists\Components\IconEntry::make('file_path')
                                    ->label(fn ($record) => $record->size > 1024 * 1024 ?
                                        'Download ('.round($record->size / (1024 * 1024), 2).' MB)' :
                                        'Download ('.round($record->size / 1024).' KB)')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->url(fn ($record): string => asset('storage/'.$record->file_path))
                                    ->openUrlInNewTab(),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Version'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),
                            ])
                            ->columns(3),

                    ])
                    ->columns(1),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bundle_limit')
                    ->label('Bundle Retention Limit')
                    ->helperText('Number of bundles to keep. Oldest will be deleted when this limit is reached. Leave empty for no limit.')
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('bundle_limit')
                    ->label('Bundle Limit')
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ?? 'Unlimited'),

                Tables\Columns\TextColumn::make('bundles_count')
                    ->label('Bundles')
                    ->badge()
                    ->color('info')
                    ->counts('bundles'),

                Tables\Columns\TextColumn::make('channels_count')
                    ->label('Channels')
                    ->badge()
                    ->color('success')
                    ->counts('channels'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('copy_id')
                    ->icon('heroicon-o-clipboard-document')
                    ->label('Copy ID')
                    ->color('gray')
                    ->action(fn () => null)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click.prevent' => "window.navigator.clipboard.writeText('{$record->uuid}'); \$tooltip('Copied to clipboard');",
                    ]),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            $data['user_id'] = Auth::id();

                            return $data;
                        }),
                    DeleteAction::make(),
                ]),
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

        return $query->where('user_id', Auth::id());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'view' => Pages\ViewApplication::route('/{record}'),
        ];
    }
}
