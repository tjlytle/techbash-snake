Feature: A square only shorter heads can reach is not danger
  In order to keep the ring instead of flinching from snakes I would beat head-to-head
  As the TechBash snake
  I treat a square as dangerous only when a head as long as me or longer can reach it

  Scenario: A shorter head next to the ring square ahead
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      ---UvV-----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: An equal-length head next to the ring square ahead
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      ---UvvV----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"

  Scenario: One head as long as us keeps the square ahead dangerous
    Given the board is:
      """
      --X--------
      --x--------
      --x--------
      --W--------
      ---UvV-----
      --S--------
      --t--------
      --t--------
      --T--------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"
