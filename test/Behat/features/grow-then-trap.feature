Feature: The snake grows until it is the longest, then uses its body to trap
  In order to win on space once it is big
  As the TechBash snake
  I hunt food until I am strictly longer than every other snake, then squeeze the nearest shorter one

  Scenario: Not yet the longest, the snake hunts food two squares away
    Given the board is:
      """
      -----------
      -----------
      -----------
      ---0-------
      -----------
      ---S-------
      ---t---UvvV
      ---T-------
      -----------
      -----------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "up"

  Scenario: Longest on the board, the snake seals the strip and ignores the food
    Given the board is:
      """
      --T--------
      --t--------
      --t--------
      Uvt--------
      -vt--------
      -Vt--------
      --t--------
      --t--------
      --t--------
      --S-0------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "down"

  Scenario: Equal length is not big, the snake keeps growing
    Given the board is:
      """
      U-T--------
      v-t--------
      v-t--------
      v-t--------
      v-t--------
      v-t--------
      v-t--------
      v-t--------
      v-t--------
      V-S-0------
      -----------
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "right"

  Scenario: A seal that would trap us too is refused
    Given the board is:
      """
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      -----------
      ---tttttttT
      ---S---UvvV
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"

  Scenario: Growing, the snake will not hunt into a pocket smaller than itself
    Given the board is:
      """
      ---------S-
      -------tttt
      ------ttttt
      ------t-ttt
      ------t-ttt
      ------ttttt
      ----------t
      ----------T
      -----------
      -----------
      ----------0
      """
    When the snake receives a "/start" request
    And the snake receives a "/move" request
    Then the snake moves "left"
