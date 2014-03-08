CREATE TABLE `resolver_dependency` (
	`version_id` int(10) unsigned NOT NULL,
	`package_version_id` int(10) unsigned NOT NULL,
	PRIMARY KEY (`version_id`,`package_version_id`),
	KEY `resolver_dependency_package` (`package_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `resolver_package` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`fullname` varchar(255) NOT NULL,
	`description` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `resolver_version` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`package_id` int(10) unsigned NOT NULL,
	`version` varchar(255) NOT NULL,
	`reference` varchar(255) NOT NULL,
	`referenceType` varchar(255) NOT NULL,
	`referenceUrl` varchar(255) NOT NULL,
	`license` varchar(255) NOT NULL,
	`createdAt` datetime NOT NULL,
	`updatedAt` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `package_id` (`package_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `resolver_dependency`
	ADD CONSTRAINT `resolver_dependency_package` 
		FOREIGN KEY (`package_version_id`) 
		REFERENCES `resolver_version` (`id`) 
		ON DELETE CASCADE 
		ON UPDATE NO ACTION,
	ADD CONSTRAINT `resolver_dependency_version` 
		FOREIGN KEY (`version_id`) 
		REFERENCES `resolver_version` (`id`) 
		ON DELETE CASCADE 
		ON UPDATE NO ACTION;

ALTER TABLE `resolver_version`
	ADD CONSTRAINT `resolver_version_package` 
		FOREIGN KEY (`package_id`) 
		REFERENCES `resolver_package` (`id`) 
		ON DELETE CASCADE 
		ON UPDATE NO ACTION;