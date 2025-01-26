<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BundleResource\Pages;
use App\Filament\Resources\BundleResource\RelationManagers;
use App\Models\Bundle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BundleResource extends Resource
{
    protected static ?string $model = Bundle::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Main';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('application_id')
                    ->relationship('application', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->placeholder('v1.0.0')
                    ->hint('This is the version of the bundle')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('file_path')
                    ->required()
                    ->maxSize(1024 * 1024 * 10) // 10MB
                    ->disk('public')
                    ->directory('bundles')
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('application.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('size')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return $state > 1024 * 1024 ? round($state / (1024 * 1024), 2) . ' MB' : round($state / 1024) . ' KB';
                    }),
                Tables\Columns\IconColumn::make('file_path')
                    ->label('File')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Bundle $record): string => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('application')
                    ->relationship('application', 'name'),
                Tables\Filters\SelectFilter::make('created_at')
                    ->label('Time Period')
                    ->options([
                        'today' => 'Today',
                        'week' => 'This Week',
                        'month' => 'This Month',
                        'year' => 'This Year',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'today' => $query->whereDate('created_at', now()),
                            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'month' => $query->whereMonth('created_at', now()->month),
                            'year' => $query->whereYear('created_at', now()->year),
                            default => $query
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBundles::route('/'),
            'create' => Pages\CreateBundle::route('/create'),
            'edit' => Pages\EditBundle::route('/{record}/edit'),
        ];
    }
}
