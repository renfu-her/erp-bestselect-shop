@servers(['localhost' => '127.0.0.1'])

@story('deploy-dev')
    g-pull-ori
    setup-pkg-dev
    reset-db-dev
    serve
@endstory

@task('g-pull-ori', ['confirm' => true])
    @if($branch)
        git checkout {{ $branch }}
        git pull origin {{ $branch }}
    @endif
@endtask

@task('setup-pkg-dev')
    rm composer.lock
    composer install
    npm install
    npm run dev
@endtask

@task('reset-db-dev')
    php artisan migrate:reset
    php artisan migrate
    php artisan db:seed
@endtask

@task('serve')
    php artisan serve
@endtask
