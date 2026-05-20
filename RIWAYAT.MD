delete (
    Route::post('/pengguna', [PageController::class, 'store'])->name('pengguna.store');
    Route::get('/pengguna/{id}/edit', [PageController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [PageController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{id}', [PageController::class, 'destroy'])->name('pengguna.destroy');
    Route::get('/project-resources', [ProjectResourceController::class, 'index'])->name('project-resources.index');
    Route::get('/project-resources/create', [ProjectResourceController::class, 'create'])->name('project-resources.create');
    Route::post('/project-resources', [ProjectResourceController::class, 'store'])->name('project-resources.store');
)