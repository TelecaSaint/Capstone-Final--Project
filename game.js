// Math Quest: Step 2 – Difficulty, Timer, and Streak System

const config = {
  type: Phaser.AUTO,
  width: 800,
  height: 600,
  backgroundColor: '#1e1e2f',
  parent: 'game',
  scene: { preload, create, update }
};

let game = new Phaser.Game(config);

// --- Game variables ---
let questionText, answerInput, feedbackText, scoreText, timerText, difficultyText, streakText;
let currentAnswer;
let score = 0;
let streak = 0;
let timeLeft = 15;
let timerEvent;
let difficulty = "Easy";

function preload() {}

function create() {
  const centerX = this.cameras.main.centerX;

  // Title
  this.add.text(centerX, 40, '📚 Math Quest', {
    fontSize: '40px',
    color: '#FFD700'
  }).setOrigin(0.5);

  // Difficulty
  difficultyText = this.add.text(centerX, 90, `Difficulty: ${difficulty}`, {
    fontSize: '22px',
    color: '#ffffff'
  }).setOrigin(0.5);

  // XP / Score
  scoreText = this.add.text(centerX, 130, 'XP: 0', {
    fontSize: '26px',
    color: '#00ffcc'
  }).setOrigin(0.5);

  // Streak display
  streakText = this.add.text(centerX, 170, '🔥 Streak: 0', {
    fontSize: '22px',
    color: '#ff8800'
  }).setOrigin(0.5);

  // Question text
  questionText = this.add.text(centerX, 240, '', {
    fontSize: '34px',
    color: '#ffffff'
  }).setOrigin(0.5);

  // Timer
  timerText = this.add.text(centerX, 300, '', {
    fontSize: '28px',
    color: '#f5576c'
  }).setOrigin(0.5);

  // Feedback
  feedbackText = this.add.text(centerX, 400, '', {
    fontSize: '26px',
    color: '#00ffcc'
  }).setOrigin(0.5);

  // Difficulty buttons
  createDifficultyButtons(this);

  // Create input box
  createAnswerInput(this);

  // Start with first question
  generateQuestion(this);
}

function createDifficultyButtons(scene) {
  const buttons = [
    { label: 'Easy', x: 250 },
    { label: 'Medium', x: 400 },
    { label: 'Hard', x: 550 }
  ];

  buttons.forEach(btn => {
    const text = scene.add.text(btn.x, 520, btn.label, {
      fontSize: '22px',
      color: '#fff',
      backgroundColor: '#333',
      padding: { x: 15, y: 8 },
      borderRadius: 10
    }).setOrigin(0.5)
      .setInteractive({ useHandCursor: true })
      .on('pointerdown', () => {
        difficulty = btn.label;
        difficultyText.setText(`Difficulty: ${difficulty}`);
        generateQuestion(scene);
      });

    text.on('pointerover', () => text.setStyle({ backgroundColor: '#555' }));
    text.on('pointerout', () => text.setStyle({ backgroundColor: '#333' }));
  });
}

function createAnswerInput(scene) {
  answerInput = document.createElement('input');
  answerInput.type = 'number';
  answerInput.placeholder = 'Enter your answer...';
  answerInput.style.position = 'absolute';
  answerInput.style.left = '50%';
  answerInput.style.top = '70%';
  answerInput.style.transform = 'translate(-50%, -50%)';
  answerInput.style.padding = '15px';
  answerInput.style.fontSize = '20px';
  answerInput.style.borderRadius = '10px';
  answerInput.style.border = '2px solid #764ba2';
  answerInput.style.textAlign = 'center';
  answerInput.style.width = '200px';
  answerInput.style.color = '#333';
  document.body.appendChild(answerInput);

  answerInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      checkAnswer(scene);
    }
  });
}

function generateQuestion(scene) {
  if (timerEvent) timerEvent.remove(false); // reset timer

  const { a, b, op, answer } = createMathProblem(difficulty);
  currentAnswer = answer;
  questionText.setText(`Solve: ${a} ${op} ${b}`);

  feedbackText.setText('');
  answerInput.value = '';
  timeLeft = 15;
  timerText.setText(`⏱ ${timeLeft}s`);

  timerEvent = scene.time.addEvent({
    delay: 1000,
    callback: () => {
      timeLeft--;
      timerText.setText(`⏱ ${timeLeft}s`);

      if (timeLeft <= 0) {
        feedbackText.setColor('#ff5555');
        feedbackText.setText(`⏰ Time's up! Answer: ${currentAnswer}`);
        streak = 0;
        streakText.setText(`🔥 Streak: ${streak}`);
        scene.time.delayedCall(1500, () => generateQuestion(scene));
      }
    },
    loop: true
  });
}

function createMathProblem(diff) {
  let a, b, op;
  switch (diff) {
    case 'Easy':
      a = Phaser.Math.Between(1, 10);
      b = Phaser.Math.Between(1, 10);
      op = Phaser.Math.RND.pick(['+', '-']);
      break;
    case 'Medium':
      a = Phaser.Math.Between(5, 20);
      b = Phaser.Math.Between(5, 15);
      op = Phaser.Math.RND.pick(['+', '-', '*']);
      break;
    case 'Hard':
      a = Phaser.Math.Between(10, 50);
      b = Phaser.Math.Between(2, 12);
      op = Phaser.Math.RND.pick(['+', '-', '*', '/']);
      break;
  }

  let answer;
  switch (op) {
    case '+': answer = a + b; break;
    case '-': answer = a - b; break;
    case '*': answer = a * b; break;
    case '/': answer = parseFloat((a / b).toFixed(2)); break;
  }

  return { a, b, op, answer };
}

function checkAnswer(scene) {
  const playerAnswer = parseFloat(answerInput.value);

  if (isNaN(playerAnswer)) {
    feedbackText.setColor('#ff5555');
    feedbackText.setText('⚠ Enter a number!');
    return;
  }

  if (Math.abs(playerAnswer - currentAnswer) < 0.01) {
    // Correct!
    streak++;
    let gainedXP = 10 * (difficulty === 'Medium' ? 1.5 : difficulty === 'Hard' ? 2 : 1);
    gainedXP += streak > 2 ? 5 : 0; // streak bonus
    score += Math.round(gainedXP);

    feedbackText.setColor('#00ffcc');
    feedbackText.setText(`✅ Correct! +${Math.round(gainedXP)} XP`);

    scoreText.setText(`XP: ${score}`);
    streakText.setText(`🔥 Streak: ${streak}`);

    scene.time.delayedCall(1000, () => generateQuestion(scene));
  } else {
    feedbackText.setColor('#ff5555');
    feedbackText.setText(`❌ Wrong! Answer: ${currentAnswer}`);
    streak = 0;
    streakText.setText(`🔥 Streak: ${streak}`);
    scene.time.delayedCall(1500, () => generateQuestion(scene));
  }
}

function update() {}
