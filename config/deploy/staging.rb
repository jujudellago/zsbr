set :stage, :staging

# Simple Role Syntax
# ==================
#role :app, %w{deploy@example.com}
#role :web, %w{deploy@example.com}
#role :db,  %w{deploy@example.com}

# Extended Server Syntax
# ======================
server "94.103.96.180", user: 'terolabs', roles: %w{web app db}






SSHKit.config.command_map[:composer] = "/usr/local/php56/bin/php /home/terolabs/utils/php/composer/composer.phar"

SSHKit.config.command_map[:wp] ="~/bin/wp.sh"

set :tmp_dir, "/home/terolabs/tmp"


set :deploy_to, -> { "/home/terolabs/tst.zermattsummit.org/staging" }


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

#set :wpcli_rsync_options, '-avz -e "ssh -p 2222"' 



set :wpcli_remote_url, @secrets_yml['staging_url']
set :wpcli_local_url, @secrets_yml['dev_url']

set :local_tmp_dir, '/tmp'
set :wpcli_backup_db, true
set :wpcli_local_db_backup_dir, 'config/backups'
set :wpcli_local_uploads_dir, 'web/app/uploads/'
set :wpcli_remote_uploads_dir, "#{shared_path.to_s}/web/app/uploads/"






