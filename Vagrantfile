#!/bin/sh
# -*- mode: ruby -*-
#
# Vagrantfile for development VM
#

# Variables to edit for each project
ROLE_NAME    = "ADP"
ORGANIZATION = "SIFIRME"

# Initalize variables
BOX_NAME     = "fiducial-centos7"
BOX_IMAGE    = "https://udp-registry.fiducial.dom/artifactory/api/vagrant/boxes/#{BOX_NAME}"
BOX_MEMORY_PROVISIONING   = 12288
BOX_MEMORY   = 4096
BOX_CPU      = 2
PLAYBOOK_GIT = "git@github.fiducial.dom:#{ORGANIZATION}/playbook-#{ROLE_NAME}.git"
PLAYBOOK_GIT_BRANCH="develop"

# We're interested in rsa & dsa keys
$key_search=%w/github_rsa id_rsa id_dsa/
$private_key = nil

# $set_environment_variables = <<SCRIPT
# tee "/etc/profile.d/myvars.sh" > "/dev/null" <<EOF
# export PROXY_HTTP="http://udp-proxy.fiducial.dom:8888/"
# export PROXY_HTTPS="http://udp-proxy.fiducial.dom:8888/"
# EOF
# SCRIPT

def provisioned?(vm_name='default', provider='virtualbox')
  File.exist?(".vagrant/machines/#{vm_name}/#{provider}/action_provision")
end

Vagrant.configure(2) do |config|
  # Check which SSH key to use
  $private_key = $key_search.select{ |x| File.exists?(File.join(Dir.home, ".ssh", x)) }.first
  $private_key = File.read(File.join(Dir.home, ".ssh", $private_key)) rescue nil

  config.vm.box     = "#{BOX_NAME}"
  config.vm.box_url = "#{BOX_IMAGE}"
  config.vm.hostname = "#{ROLE_NAME}"
  config.vm.network :forwarded_port, guest: 80, host: 80, auto_correct: true
  config.vm.network :forwarded_port, guest: 443, host: 443, auto_correct: true
  config.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true
  config.vm.network :forwarded_port, guest: 9000, host: 9000, auto_correct: true
  config.vm.network :forwarded_port, guest: 5432, host: 5432, auto_correct: true
  config.vm.network :forwarded_port, guest: 8025, host: 8025, auto_correct: true

  config.ssh.forward_agent = true
  config.ssh.username = 'root'
  config.ssh.password = 'vagrant'
  config.ssh.insert_key = 'true'
  config.vm.box_download_insecure = true

#  if Vagrant.has_plugin?("vagrant-proxyconf")
#    config.proxy.http     = "#{PROXY_HTTP}"
#    config.proxy.https    = "#{PROXY_HTTPS}"
#    config.proxy.no_proxy = "lxlyogfw30.fiducial.dom, svlyoref10.fiducial.dom"
#  end

  config.vm.provider "virtualbox" do |v|
    v.name   = "#{ROLE_NAME}"
    v.cpus   = "#{BOX_CPU}"
    if provisioned?
      v.memory = "#{BOX_MEMORY}"
    else
      v.memory ="#{BOX_MEMORY_PROVISIONING}"
    end
    v.linked_clone = true
    v.customize ['modifyvm', :id, '--cableconnected1', 'on']
  end

  # SSH setup
  config.vm.provision(:shell,
                      :inline => "mkdir -p /root/.ssh && echo '#{$private_key}' > /root/.ssh/id_rsa && chmod 600 /root/.ssh/id_rsa") if $private_key

  config.vm.provision(:shell,
                      :privileged => false,
                      :inline => "mkdir -p /home/vagrant/.ssh && echo '#{$private_key}' > /home/vagrant/.ssh/id_rsa && chmod 600 /home/vagrant/.ssh/id_rsa") if $private_key

#   config.vm.provision "shell", inline: $set_environment_variables, run: "always"

    config.vm.provision(:shell,
                        :privileged => false,
                        env: {"PLAYBOOK_GIT_BRANCH" => "#{PLAYBOOK_GIT_BRANCH}"},
                        :inline => "playbook-specs #{PLAYBOOK_GIT}")

    config.vm.provision(:shell,
                        :inline => "yum install -y php74-php-pecl-xdebug3.x86_64")

    config.vm.provision(:shell,
                        :inline => "composer self-update")

    config.vm.provision "file", source: "vagrant/xdebug.ini", destination: "/etc/opt/remi/php74/php.d/16-xdebug.ini"
    config.vm.provision "file", source: "vagrant/mailhog.ini", destination: "/etc/opt/remi/php74/php.d/17-mailhog.ini"
    config.vm.provision "file", source: "vagrant/.bashrc", destination: "/var/cache/nginx/.bashrc"

    config.vm.provision(:shell,
                          :inline => "dos2unix /var/cache/nginx/.bashrc")

    config.vm.provision(:shell,
                        :inline => "chown -R nginx:nginx /data/livraisons/sources/current/")

    config.vm.provision(:shell,
                        :inline => "echo '10.69.207.22 lxlyodiv33' >> /etc/hosts")
    ### MAILHOG Install
    config.vm.provision "file", source: "vagrant/mailhog.service", destination: "/etc/systemd/system/mailhog.service"

    config.vm.provision(:shell, :path => "vagrant/install_mailhog.sh")
    ### End Mailhog Install
end
