<?php

namespace Tig\Storage\User;

/**
 * Interface UserRepository
 *
 * This is an implementation of the Repository pattern, which abstracts the
 * data access layer from the object and object collection logic.
 *
 * @package Tig\Storage\User
 */
interface UserRepository {

  /**
   * Get all users.
   *
   * @return array
   */
  public function all();

  /**
   * Find user by ID.
   *
   * @param int $id
   * @return mixed
   */
  public function find($id);

  /**
   * Find by other search criteria (email, etc.)
   *
   * @param array $search
   * @return mixed
   */
  public function findBy($search);

  /**
   * @param array $data
   * @return int              ID of the created user.
   */
  public function create($data);

  /**
   * Update existing user.
   *
   * @param int $id
   * @param array $data
   */
  public function update($id, $data);

  /**
   * Delete by ID.
   *
   * @param int $id
   */
  public function delete($id);

  /**
   * @param $email
   * @param $password
   * @return mixed
   */
  public function login($email, $password);
}
