<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use UnitEnum;
use BackedEnum;

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
                            ->schema([
                                Infolists\Components\IconEntry::make('file_path')
                                    ->label(fn($record) => $record->size > 1024 * 1024 ?
                                        'Download (' . round($record->size / (1024 * 1024), 2) . ' MB)' :
                                        'Download (' . round($record->size / 1024) . ' KB)')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->url(fn($record): string => asset('storage/' . $record->file_path))
                                    ->openUrlInNewTab(),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Version'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),
                            ])
                            ->columns(3)

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('user.name'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        ];
    }
}
