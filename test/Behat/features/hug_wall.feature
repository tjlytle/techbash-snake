Feature: Snake runs around the outside of the board
  In order to stay out of the crowd
  As the TechBash snake
  I head for the nearest wall, follow it round, and only leave it when I must

  Scenario: Heads for the nearest wall on an open board
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      ---Stt-----
      -----T-----
      -----------
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"

  Scenario: Steps off the wall when the way along it is blocked
    Given the board is:
      """
      -----------
      -----------
      -----------
      UvvV-------
      S----------
      t----------
      t----------
      T----------
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"

  Scenario: Turns back toward the wall once it has stepped off
    Given the board is:
      """
      -----------
      -----------
      -----------
      UvvV-------
      tS---------
      t----------
      t----------
      T----------
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "down"

  Scenario: Grabs food that is right beside it, even off the wall
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      S0---------
      t----------
      t----------
      T----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"
