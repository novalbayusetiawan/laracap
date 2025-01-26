<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Main';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
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

    public static function form(Form $form): Form
    {
        return $form
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            $data['user_id'] = Auth::id();

                            return $data;
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
