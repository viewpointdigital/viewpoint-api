<?php
/**
 * Define API Pagination
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

/**
 * Define API Pagination to merge two endpoints.
 */
class Pagination {

	/**
	 * Create pagination settings.
	 *
	 * @param bool  $isPaginated Is request paginaged.
	 * @param int   $totalPages Total number of pages. Defaults to high number so we run a request first.
	 * @param int   $currentPage Current page number.
	 * @param array $pageParam The query param to modify on pagination.
	 */
	public function __construct(
		public bool $isPaginated = false,
		public int $totalPages = 999,
		public int $currentPage = 0,
		public array $pageParam = [],
	) {
	}

	/**
	 * Has more pages?
	 *
	 * @return boolean
	 */
	public function hasMorePages() {
		return $this->currentPage < $this->totalPages;
	}
}
