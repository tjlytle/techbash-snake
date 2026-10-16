Feature: The snake is hungry
  In order to grow
  As the snake
  I head for the closest food on the board, every turn

  Scenario: Goes for the closer of two foods
    Given the board is:
      """
      -----------
      -----0-----
      -----------
      -----------
      -----------
      -----S-0---
      -----t-----
      -----T-----
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"

  Scenario: Steps around what is in the way
    Given the board is:
      """
      -----------
      -----------
      -----------
      -0---------
      -----------
      StT--------
      -----------
      -----------
      -----------
      0----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: No food on the board
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      -----S-----
      -----t-----
      -----T-----
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves in some valid direction
