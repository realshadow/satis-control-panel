<?php

namespace App\Satis\Model;

use App\Satis\Collections\RepositoryCollection;
use App\Satis\Collections\PackageCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Configuration class
 *
 * Represent a satis configuration file
 *
 * @author Lukas Homza <lukashomz@gmail.com>
 */
class Config {
	/**
	 * @Type("string")
	 */
	private $name;

	/**
	 * @Type("string")
	 */
	private $homepage;

	/**
	 * @Type("App\Satis\Collections\RepositoryCollection<App\Satis\Model\Repository>")
	 */
	private $repositories;

	/**
	 * @Type("App\Satis\Collections\PackageCollection<App\Satis\Model\Package>")
	 * @SerializedName("require")
	 */
	private $packages;

	/**
	 * @Type("boolean")
	 * @SerializedName("require-all")
	 */
	private $requireAll;

	/**
	 * @Type("boolean")
	 * @SerializedName("require-dependencies")
	 */
	private $requireDependencies;

	/**
	 * @Type("boolean")
	 * @SerializedName("require-dev-dependencies")
	 */
	private $requireDevDependencies;

	/**
	 * @var Archive
	 * @Type("App\Satis\Model\Archive")
	 */
	private $archive;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->repositories = [];
		$this->packages = [];
		$this->requireAll = false;
		$this->requireDependencies = true;
		$this->requireDevDependencies = false;
	}

	/**
	 * Get name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get homepage
	 *
	 * @return string $homepage
	 */
	public function getHomepage() {
		return $this->homepage;
	}

	/**
	 * @param string $homepage
	 * @return $this
	 */
	public function setHomepage($homepage) {
		$this->homepage = $homepage;

		return $this;
	}

	/**
	 * Get repositories
	 *
	 * @return RepositoryCollection<Repository>
	 */
	public function getRepositories() {
		return $this->repositories;
	}

	/**
	 * Set repositories
	 *
	 * @param RepositoryCollection $repositories
	 *
	 * @return static
	 */
	public function setRepositories(RepositoryCollection $repositories) {
		$this->repositories = $repositories;

		return $this;
	}

	/**
	 * @param Archive $archive
	 */
	public function setArchive(Archive $archive = null) {
		$this->archive = $archive;
	}

	/**
	 * @return Archive
	 */
	public function getArchive() {
		return $this->archive;
	}

	/**
	 * Get packages
	 *
	 * @return PackageCollection<Package>
	 */
	public function getPackages() {
		return $this->packages;
	}

	/**
	 * @param PackageCollection $packages
	 * @return $this
	 */
	public function setPackages(PackageCollection $packages) {
		$this->packages = $packages;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequireDevDependencies() {
		return $this->requireDevDependencies;
	}

	/**
	 * @param boolean $requireDevDependencies
	 * @return Config
	 */
	public function setRequireDevDependencies($requireDevDependencies) {
		$this->requireDevDependencies = $requireDevDependencies;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequireAll() {
		return $this->requireAll;
	}

	/**
	 * @param boolean $requireAll
	 * @return $this
	 */
	public function setRequireAll($requireAll) {
		$this->requireAll = $requireAll;

		return $this;
	}
}
