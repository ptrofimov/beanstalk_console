# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
	config.vm.box = "hashicorp/precise64"
	config.vm.box_url = "https://vagrantcloud.com/hashicorp/boxes/precise64/versions/1.1.0/providers/virtualbox.box"

	# expose ports
	config.vm.network :forwarded_port, host: 7654, guest: 80

	# Setup shared dir with www-data owner
	config.vm.synced_folder ".", "/vagrant", :user => "www-data", :group => "www-data"

	## Run provision setup script
	config.vm.provision "shell", :path => "vagrant/provision.sh"
end
