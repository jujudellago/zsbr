set :stage, :staging

# Simple Role Syntax
# ==================
#role :app, %w{deploy@example.com}
#role :web, %w{deploy@example.com}
#role :db,  %w{deploy@example.com}

# Extended Server Syntax
# ======================
server "108.179.246.134", user: 'jujudalp', roles: %w{web app db}, port: 2222



SSHKit.config.command_map[:composer] = "php /home4/jujudalp/utils/php/composer/composer.phar"

set :tmp_dir, "/home4/jujudalp/tmp"


set :deploy_to, -> { "/home4/jujudalp/www/wittekop.gs-projets.ch" }

# you can set custom ssh options
# it's possible to pass any option but you need to keep in mind that net/ssh understand limited list of options
# you can see them in [net/ssh documentation](http://net-ssh.github.io/net-ssh/classes/Net/SSH.html#method-c-start)
# set it globally
#  set :ssh_options, {
#    keys: %w(~/.ssh/id_rsa),
#    forward_agent: false,
#    auth_methods: %w(password)
#  }

fetch(:default_env).merge!(wp_env: :staging)

set :wpcli_remote_url, @secrets_yml['test_url']
set :wpcli_local_url, @secrets_yml['dev_url']

set :local_tmp_dir, '/tmp'
set :wpcli_backup_db, true
set :wpcli_local_db_backup_dir, 'config/backups'
set :wpcli_local_uploads_dir, 'web/app/uploads/'
set :wpcli_remote_uploads_dir, "#{shared_path.to_s}/web/app/uploads/"






