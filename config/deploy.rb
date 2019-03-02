set :application, 'zsbr'
set :repo_url, 'git@github.com:jujudellago/zsbr.git'

# Branch options
# Prompts for the branch name (defaults to current branch)
#ask :branch, -> { `git rev-parse --abbrev-ref HEAD`.chomp }

# Hardcodes branch to always be master
# This could be overridden in a stage config file
set :branch, :master
@secrets_yml ||= YAML.load_file(File.expand_path("secrets.yml", __dir__))

     

SSHKit.config.command_map[:composer] = "/usr/local/php56/bin/php -c /home/terolabs/tst.zermattsummit.org/utils/php/php.ini /home/terolabs/utils/php/composer/composer.phar"

SSHKit.config.command_map[:wp] ="~/bin/wp.sh"

set :tmp_dir, "/home/terolabs/tmp"

# Use :debug for more verbose output when troubleshooting
set :log_level, :info

# Apache users with .htaccess files:
# it needs to be added to linked_files so it persists across deploys:
set :linked_files, fetch(:linked_files, []).push('.env', 'web/.htaccess')
#set :linked_files, fetch(:linked_files, []).push('.env')
set :linked_dirs, fetch(:linked_dirs, []).push('web/app/uploads')



namespace :deploy do
  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      # Your restart mechanism here, for example:
      # execute :service, :nginx, :reload
    end
  end
end

# The above restart task is not run by default
# Uncomment the following line to run it on deploys if needed
# after 'deploy:publishing', 'deploy:restart'

namespace :deploy do
  desc 'Update WordPress template root paths to point to the new release'
  task :update_option_paths do
    on roles(:app) do
      within fetch(:release_path) do
        if test :wp, :core, 'is-installed'
          [:stylesheet_root, :template_root].each do |option|
            # Only change the value if it's an absolute path
            # i.e. The relative path "/themes" must remain unchanged
            # Also, the option might not be set, in which case we leave it like that
            value = capture :wp, :option, :get, option, raise_on_non_zero_exit: false
            if value != '' && value != '/themes'
              execute :wp, :option, :set, option, fetch(:release_path).join('web/wp/wp-content/themes')
            end
          end
        end
      end
    end
  end
end

# The above update_option_paths task is not run by default
# Note that you need to have WP-CLI installed on your server
# Uncomment the following line to run it on deploys if needed
# after 'deploy:publishing', 'deploy:update_option_paths'




# The Roots theme by default does not check production assets into Git, so
# they are not deployed by Capistrano when using the Bedrock stack. The
# following will compile and deploy those assets. Copy this to the bottom of
# your config/deploy.rb file.

# Based on information from this thread:
# http://discourse.roots.io/t/capistrano-run-grunt-locally-and-upload-files/2062/7
# and specifically this gist from christhesoul:
# https://gist.github.com/christhesoul/3c38053971a7b786eff2

# First we need to set some variables so we know where things are. You should
# only have to modify :theme_path here, :local_app_path and :local_theme_path
# are set from that.
set :theme_path, Pathname.new('web/app/themes/zermatt')
set :local_app_path, Pathname.new(File.dirname(__FILE__)).join('../')
set :local_theme_path, fetch(:local_app_path).join(fetch(:theme_path))

# Next we list the production assets we want to deploy. We could change things
# around so that all our production assets are generated into a single
# directory and upload that, but it would involve touching a lot of things.
# Listing them each explicitly keeps our changes to just the deployment
# configuration.
#set :production_assets, [
#  'dist/styles/main.css',
#  'dist/styles/main.css.map',  
#  'assets/js/vendor/modernizr.min.js',
#  'assets/vendor/jquery/dist/jquery.min.js',
#  'assets/vendor/bootstrap/fonts/glyphicons-halflings-regular.eot',
#  'assets/vendor/bootstrap/fonts/glyphicons-halflings-regular.woff',
#  'assets/vendor/bootstrap/fonts/glyphicons-halflings-regular.ttf',
#  'assets/vendor/bootstrap/fonts/glyphicons-halflings-regular.svg'].map {|path| Pathname.new(path) }

#       namespace :deploy do
#       
#         # The :compile_assets task will run 'grunt build' in our theme directory
#         # to build the production assets.
#         task :compile_assets do
#           run_locally do
#             within fetch(:local_theme_path) do
#               execute :gulp, :build
#             end
#           end
#         end
#       
#         # The :copy_assets task first runs :compile_assets, then goes through the
#         # list of production assets and uploads them to the server. It also creates
#         # the target directories if necessary.
#         task :copy_assets do
#           invoke 'deploy:compile_assets'
#           on roles(:web) do
#             fetch(:production_assets).each do |path|
#               execute :mkdir, "-p", release_path.join(fetch(:theme_path)).join(path.dirname())
#            #   upload! fetch(:local_theme_path).join('assets/vendor/bootstrap/dist/fonts/.').to_s, release_path.join(fetch(:theme_path)).join('assets/fonts'), recursive: true
#           #    upload! fetch(:local_theme_path).join('dist/fonts/.').to_s, release_path.join(fetch(:theme_path)).join('dist/fonts'), recursive: true
#               upload! fetch(:local_theme_path).join('dist').to_s, release_path.join(fetch(:theme_path)), recursive: true
#                
#               #upload! fetch(:local_theme_path).join(path).to_s, release_path.join(fetch(:theme_path)).join(path)
#             end
#           end
#         end
#       end

# This tells Capistrano to copy our production assets to the server after it
# has created the release directory but before it has published the release.
#  before "deploy:updated", "deploy:copy_assets"



namespace :deploy do
  task :set_rights do
    on roles(:web) do
      execute :find, release_path.to_s, ' -type d -name  "*" -exec chmod 755 {} \;'
      execute :find, release_path.to_s, ' -type f -name  "*" -exec chmod 644 {} \;'
    end
  end
end

after "deploy:updated", "deploy:set_rights"