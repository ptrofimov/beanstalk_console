# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
	config.vm.box = "precise64"
	config.vm.box_url = "http://files.vagrantup.com/precise64.box"

	# expose ports
	config.vm.network :forwarded_port, host: 7654, guest: 80

	# Setup shared dir with www-data owner
	config.vm.synced_folder ".", "/vagrant", :user => "www-data", :group => "www-data"

	## Run provision setup script
	config.vm.provision "shell", :path => "vagrant/provision.sh"
end
