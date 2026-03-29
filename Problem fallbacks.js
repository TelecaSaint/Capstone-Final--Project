// ── Expanded fallback bank (replaces the existing const fallbacks = {...} block) ──
const fallbacks = {

  algebra: {
    easy: [
      {type:'Solve for x',      question:'x + 9 = 15',              answer:'6',     hint:'Subtract 9 from both sides.',        explanation:'x+9=15\nx=15−9\nx=6'},
      {type:'Solve for x',      question:'2x = 14',                  answer:'7',     hint:'Divide both sides by 2.',             explanation:'2x=14\nx=14÷2\nx=7'},
      {type:'Solve for x',      question:'x − 4 = 11',              answer:'15',    hint:'Add 4 to both sides.',               explanation:'x−4=11\nx=11+4\nx=15'},
      {type:'Evaluate',         question:'If y = 3, what is 4y + 2?',answer:'14',   hint:'Substitute y=3 first.',              explanation:'4(3)+2=12+2=14'},
      {type:'Solve for x',      question:'x ÷ 5 = 6',               answer:'30',    hint:'Multiply both sides by 5.',          explanation:'x÷5=6\nx=6×5\nx=30'},
    ],
    medium: [
      {type:'Solve for x',      question:'3x + 7 = 22',             answer:'5',     hint:'Subtract 7 first, then divide.',     explanation:'3x+7=22\n3x=15\nx=5'},
      {type:'Solve for x',      question:'2x − 5 = 13',             answer:'9',     hint:'Add 5 to both sides first.',         explanation:'2x−5=13\n2x=18\nx=9'},
      {type:'Solve for x',      question:'4x + 3 = 19',             answer:'4',     hint:'Subtract 3, then divide by 4.',      explanation:'4x+3=19\n4x=16\nx=4'},
      {type:'Evaluate',         question:'Simplify: 3(x+4) when x=2',answer:'18',  hint:'Distribute first or substitute.',    explanation:'3(2+4)=3(6)=18'},
      {type:'Solve for x',      question:'5x − 10 = 20',            answer:'6',     hint:'Add 10 to both sides first.',        explanation:'5x−10=20\n5x=30\nx=6'},
    ],
    hard: [
      {type:'Two-step',         question:'2(x + 3) = 14',           answer:'4',     hint:'Distribute 2, then solve.',          explanation:'2x+6=14\n2x=8\nx=4'},
      {type:'Two variables',    question:'If 2x + y = 10 and x = 3, find y', answer:'4', hint:'Substitute x=3.',           explanation:'2(3)+y=10\n6+y=10\ny=4'},
      {type:'Solve for x',      question:'3(2x − 1) = 15',          answer:'3',     hint:'Distribute then isolate x.',        explanation:'6x−3=15\n6x=18\nx=3'},
      {type:'Inequality',       question:'What is the smallest integer where 4x > 20?', answer:'6', hint:'Solve 4x>20 then find integer.', explanation:'4x>20\nx>5\nSmallest integer=6'},
      {type:'Systems',          question:'x + y = 9 and x − y = 3. Find x.', answer:'6', hint:'Add the two equations together.', explanation:'2x=12\nx=6'},
    ],
  },

  geometry: {
    easy: [
      {type:'Find the area',    question:'Rectangle 12cm × 8cm. Area=?',  answer:'96',   hint:'Area = length × width.',        explanation:'12×8=96 cm²'},
      {type:'Find the area',    question:'Square with side 7cm. Area=?',   answer:'49',   hint:'Area = side².',                 explanation:'7²=49 cm²'},
      {type:'Perimeter',        question:'Rectangle 10m × 4m. Perimeter=?',answer:'28',  hint:'P = 2(l+w).',                   explanation:'2(10+4)=2(14)=28 m'},
      {type:'Angles',           question:'A triangle has angles 60° and 70°. Third angle=?', answer:'50', hint:'Angles in a triangle sum to 180°.', explanation:'180−60−70=50°'},
      {type:'Perimeter',        question:'Equilateral triangle, side 9cm. Perimeter=?', answer:'27', hint:'All 3 sides are equal.', explanation:'9×3=27 cm'},
    ],
    medium: [
      {type:'Find the area',    question:'Triangle with base 10cm and height 6cm. Area=?', answer:'30', hint:'Area = ½ × base × height.', explanation:'½×10×6=30 cm²'},
      {type:'Circles',          question:'Circle with radius 7cm. Circumference=? (Use π≈3.14, round to nearest whole)', answer:'44', hint:'C = 2πr.', explanation:'2×3.14×7≈43.96≈44 cm'},
      {type:'Pythagorean',      question:'Right triangle: legs 3cm and 4cm. Hypotenuse=?', answer:'5', hint:'a²+b²=c².', explanation:'3²+4²=9+16=25\n√25=5 cm'},
      {type:'Area',             question:'Parallelogram: base 8m, height 5m. Area=?', answer:'40', hint:'Area = base × height.', explanation:'8×5=40 m²'},
      {type:'Angles',           question:'Two angles are supplementary. One is 65°. Other=?', answer:'115', hint:'Supplementary angles sum to 180°.', explanation:'180−65=115°'},
    ],
    hard: [
      {type:'Circles',          question:'Circle area = 78.5 cm². Radius=? (Use π≈3.14)', answer:'5', hint:'A=πr², solve for r.', explanation:'78.5=3.14×r²\nr²=25\nr=5 cm'},
      {type:'Composite shape',  question:'L-shape: 10×8 minus 4×3 rectangle cut from corner. Area=?', answer:'68', hint:'Subtract the cut-out area.', explanation:'10×8=80\n4×3=12\n80−12=68 cm²'},
      {type:'Pythagorean',      question:'Hypotenuse is 13, one leg is 5. Other leg=?', answer:'12', hint:'a²+b²=c².', explanation:'5²+b²=13²\n25+b²=169\nb²=144\nb=12'},
      {type:'Volume',           question:'Rectangular box: 4cm × 3cm × 5cm. Volume=?', answer:'60', hint:'V = l × w × h.', explanation:'4×3×5=60 cm³'},
      {type:'Scale factor',     question:'Triangle sides are 3,4,5. Similar triangle has hypotenuse 20. Shortest side=?', answer:'12', hint:'Scale factor = 20÷5=4.', explanation:'Scale=20÷5=4\nShortest=3×4=12'},
    ],
  },

  fractions: {
    easy: [
      {type:'Simplify',         question:'Simplify: 6/8',            answer:'3/4',   hint:'Divide numerator and denominator by 2.', explanation:'GCF(6,8)=2\n6÷2=3, 8÷2=4\n=3/4'},
      {type:'Add fractions',    question:'1/4 + 1/4 = ?',            answer:'1/2',   hint:'Same denominator — just add tops.', explanation:'1/4+1/4=2/4=1/2'},
      {type:'Compare',          question:'Which is bigger: 3/4 or 2/3?', answer:'3/4', hint:'Convert to same denominator.', explanation:'3/4=9/12, 2/3=8/12\n9/12>8/12, so 3/4'},
      {type:'Fraction of',      question:'What is 1/2 of 20?',       answer:'10',    hint:'Multiply 20 × 1/2.',              explanation:'20×½=10'},
      {type:'Subtract',         question:'3/5 − 1/5 = ?',            answer:'2/5',   hint:'Same denominator — subtract tops.', explanation:'3/5−1/5=2/5'},
    ],
    medium: [
      {type:'Add fractions',    question:'3/4 + 1/8 = ?',            answer:'7/8',   hint:'Common denominator is 8.',         explanation:'6/8+1/8=7/8'},
      {type:'Multiply',         question:'2/3 × 3/4 = ?',            answer:'1/2',   hint:'Multiply tops, multiply bottoms, simplify.', explanation:'(2×3)/(3×4)=6/12=1/2'},
      {type:'Divide',           question:'3/4 ÷ 1/2 = ?',            answer:'3/2',   hint:'Multiply by the reciprocal.',      explanation:'3/4×2/1=6/4=3/2'},
      {type:'Mixed number',     question:'Convert 7/4 to a mixed number.',answer:'1 3/4', hint:'Divide 7÷4.',               explanation:'7÷4=1 remainder 3\n=1 3/4'},
      {type:'Subtract',         question:'5/6 − 1/4 = ?',            answer:'7/12',  hint:'Common denominator is 12.',        explanation:'10/12−3/12=7/12'},
    ],
    hard: [
      {type:'Complex fraction', question:'(1/2 + 1/3) ÷ 5/6 = ?',   answer:'1',     hint:'Add fractions first, then divide.', explanation:'1/2+1/3=5/6\n5/6÷5/6=1'},
      {type:'Percentage',       question:'What percent is 3/8?',      answer:'37.5',  hint:'Divide 3÷8 and multiply by 100.',  explanation:'3÷8=0.375\n×100=37.5%'},
      {type:'Word problem',     question:'A recipe needs 2/3 cup sugar. You make 1.5× the recipe. How much sugar?', answer:'1', hint:'Multiply 2/3 × 3/2.', explanation:'2/3×3/2=6/6=1 cup'},
      {type:'Ratio',            question:'Ratio 3:5. If total is 40, larger share=?', answer:'25', hint:'5 parts out of 8 total parts.', explanation:'8 parts total\n1 part=5\nLarger=5×5=25'},
      {type:'Mixed operations', question:'2 1/2 + 1 3/4 = ?',        answer:'4 1/4', hint:'Convert to improper fractions.', explanation:'5/2+7/4=10/4+7/4=17/4=4 1/4'},
    ],
  },

  wordproblems: {
    easy: [
      {type:'Word problem',     question:'Jake has 24 apples. He gives 9 to friends. How many remain?', answer:'15', hint:'This is subtraction.', explanation:'24−9=15 apples'},
      {type:'Word problem',     question:'A bus has 8 rows with 4 seats each. Total seats=?', answer:'32', hint:'Multiply rows by seats per row.', explanation:'8×4=32 seats'},
      {type:'Word problem',     question:'Maria earns $6/hr. She works 5 hours. Total earned=?', answer:'30', hint:'Multiply rate by hours.', explanation:'$6×5=$30'},
      {type:'Word problem',     question:'Tom reads 12 pages/day. How many days for 60 pages?', answer:'5', hint:'Divide total pages by pages per day.', explanation:'60÷12=5 days'},
      {type:'Word problem',     question:'A bag has 5 red and 7 blue marbles. Total=?', answer:'12', hint:'Just add them together.', explanation:'5+7=12 marbles'},
    ],
    medium: [
      {type:'Word problem',     question:'A store sells shirts for $15 each. With 20% off, new price=?', answer:'12', hint:'20% of $15 = $3 discount.', explanation:'20%×$15=$3\n$15−$3=$12'},
      {type:'Word problem',     question:'Train travels 60 mph for 2.5 hours. Distance=?', answer:'150', hint:'Distance = speed × time.', explanation:'60×2.5=150 miles'},
      {type:'Word problem',     question:'Class has 30 students. 2/5 are girls. How many girls?', answer:'12', hint:'Multiply 30 by 2/5.', explanation:'30×2/5=60/5=12 girls'},
      {type:'Word problem',     question:'A pizza costs $12 split equally by 4 people. Each person pays=?', answer:'3', hint:'Divide total by number of people.', explanation:'$12÷4=$3'},
      {type:'Word problem',     question:'Temperature at 8am: −3°C. By noon it rose 11°C. Noon temp=?', answer:'8', hint:'Add the rise to the starting temp.', explanation:'−3+11=8°C'},
    ],
    hard: [
      {type:'Word problem',     question:'A pool holds 2400L. It fills at 80L/min but leaks at 20L/min. Minutes to fill=?', answer:'40', hint:'Net fill rate = 80−20.', explanation:'Net rate=60L/min\n2400÷60=40 min'},
      {type:'Word problem',     question:'Two numbers sum to 50 and differ by 14. Larger number=?', answer:'32', hint:'If x+y=50 and x−y=14, add equations.', explanation:'2x=64\nx=32'},
      {type:'Word problem',     question:'A rectangle\'s length is 3× its width. Perimeter=48cm. Width=?', answer:'6', hint:'Express length in terms of width.', explanation:'l=3w\n2(3w+w)=48\n8w=48\nw=6'},
      {type:'Word problem',     question:'Sarah invests $500 at 4% simple interest/year. Total after 3 years=?', answer:'560', hint:'Interest = P×r×t.', explanation:'I=500×0.04×3=$60\nTotal=$500+$60=$560'},
      {type:'Percent change',   question:'Price rose from $40 to $52. Percent increase=?', answer:'30', hint:'(change÷original)×100.', explanation:'(52−40)÷40×100\n=12÷40×100\n=30%'},
    ],
  },

  arithmetic: {
    easy: [
      {type:'Calculate',        question:'256 + 189 = ?',             answer:'445',   hint:'Add column by column.',             explanation:'256+189=445'},
      {type:'Calculate',        question:'500 − 237 = ?',             answer:'263',   hint:'Borrow from the hundreds.',         explanation:'500−237=263'},
      {type:'Calculate',        question:'17 × 6 = ?',                answer:'102',   hint:'Think 10×6 + 7×6.',                explanation:'60+42=102'},
      {type:'Calculate',        question:'144 ÷ 12 = ?',              answer:'12',    hint:'12 times what = 144?',              explanation:'12×12=144'},
      {type:'Order of ops',     question:'6 + 4 × 2 = ?',            answer:'14',    hint:'Multiply before adding (PEMDAS).',  explanation:'4×2=8\n6+8=14'},
    ],
    medium: [
      {type:'Calculate',        question:'48 × 25 = ?',               answer:'1200',  hint:'48 × 100 ÷ 4.',                   explanation:'4800÷4=1200'},
      {type:'Order of ops',     question:'(3 + 5)² − 10 = ?',        answer:'54',    hint:'Brackets first, then exponent.',   explanation:'8²−10=64−10=54'},
      {type:'Calculate',        question:'15% of 80 = ?',             answer:'12',    hint:'10% + 5% of 80.',                  explanation:'10%=8, 5%=4\n8+4=12'},
      {type:'Calculate',        question:'√144 = ?',                  answer:'12',    hint:'What number squared = 144?',       explanation:'12×12=144\n√144=12'},
      {type:'Prime',            question:'Is 97 a prime number? (yes/no)', answer:'yes', hint:'Try dividing by 2,3,5,7.', explanation:'97÷2,3,5,7 → none divide evenly\n97 is prime'},
    ],
    hard: [
      {type:'Order of ops',     question:'3² + (12 ÷ 4) × 5 − 1 = ?', answer:'23', hint:'PEMDAS: exponent, brackets, ×, +−.', explanation:'9+(3×5)−1\n=9+15−1\n=23'},
      {type:'LCM',              question:'LCM of 12 and 18 = ?',      answer:'36',    hint:'List multiples of each.',           explanation:'Multiples of 12: 12,24,36\nMultiples of 18: 18,36\nLCM=36'},
      {type:'GCF',              question:'GCF of 48 and 60 = ?',      answer:'12',    hint:'Factor both numbers.',              explanation:'48=2⁴×3, 60=2²×3×5\nGCF=2²×3=12'},
      {type:'Scientific notation', question:'Write 45,000 in scientific notation.', answer:'4.5 × 10^4', hint:'Move decimal to get a number 1-10.', explanation:'45000=4.5×10000\n=4.5×10⁴'},
      {type:'Calculate',        question:'2³ × 5² = ?',               answer:'200',   hint:'Calculate each power separately.', explanation:'2³=8, 5²=25\n8×25=200'},
    ],
  },

  statistics: {
    easy: [
      {type:'Mean',             question:'4, 8, 6, 10, 7. Mean=?',   answer:'7',     hint:'Sum all, divide by count.',         explanation:'35÷5=7'},
      {type:'Median',           question:'Find median: 3, 7, 2, 9, 5', answer:'5',   hint:'Sort first, then find middle.',     explanation:'Sorted: 2,3,5,7,9\nMedian=5'},
      {type:'Mode',             question:'Mode of: 4, 2, 4, 7, 2, 4', answer:'4',   hint:'Most frequent value.',              explanation:'4 appears 3 times\nMode=4'},
      {type:'Range',            question:'Range of: 12, 5, 18, 9, 3', answer:'15',  hint:'Max − Min.',                        explanation:'18−3=15'},
      {type:'Probability',      question:'Bag has 3 red, 2 blue balls. P(red)=? (as fraction)', answer:'3/5', hint:'Favorable outcomes ÷ total.', explanation:'3÷(3+2)=3/5'},
    ],
    medium: [
      {type:'Mean',             question:'Test scores: 72, 85, 90, 68, 95. Mean=?', answer:'82', hint:'Sum÷5.', explanation:'(72+85+90+68+95)=410\n410÷5=82'},
      {type:'Median',           question:'Find median: 14, 22, 8, 31, 19, 27', answer:'20.5', hint:'Even count — average the two middle values.', explanation:'Sorted: 8,14,19,22,27,31\n(19+22)÷2=20.5'},
      {type:'Probability',      question:'Roll a die. P(even number)=? (as fraction)', answer:'1/2', hint:'Count even numbers on a die.', explanation:'Even: 2,4,6 → 3 out of 6\n=1/2'},
      {type:'Probability',      question:'P(A)=0.4, P(B)=0.3, independent. P(A and B)=?', answer:'0.12', hint:'Multiply independent probabilities.', explanation:'0.4×0.3=0.12'},
      {type:'Mean',             question:'Mean of 5 numbers is 14. Four are: 10,12,16,18. Fifth=?', answer:'14', hint:'Total = mean×count. Find missing.', explanation:'Total=14×5=70\n70−(10+12+16+18)=70−56=14'},
    ],
    hard: [
      {type:'Weighted mean',    question:'Scores: 80(×2), 90(×3), 70(×1). Weighted mean=?', answer:'84', hint:'Sum(score×weight) ÷ sum(weights).', explanation:'(160+270+70)÷6=500÷6≈83.3\n≈84 (rounded)'},
      {type:'Probability',      question:'Two cards drawn from 52. P(both aces) = ? (nearest hundredth)', answer:'0.00', hint:'(4/52)×(3/51).', explanation:'4/52×3/51=12/2652≈0.0045\n≈0.00 (to 2 d.p.)'},
      {type:'Standard question',question:'Data: 5,5,5,5,5. What is the standard deviation?', answer:'0', hint:'No spread means SD=0.', explanation:'All values are equal\nNo deviation from mean\nSD=0'},
      {type:'Compound prob',    question:'Flip 3 fair coins. P(all heads)=? (as fraction)', answer:'1/8', hint:'½ × ½ × ½.',                        explanation:'(1/2)³=1/8'},
      {type:'Outlier effect',   question:'Data: 10,12,11,13,100. Does removing 100 increase or decrease the mean? (increase/decrease)', answer:'decrease', hint:'100 pulls the mean up.', explanation:'With 100: mean=29.2\nWithout 100: mean=11.5\nRemoving it decreases mean'},
    ],
  },

};

// ── Updated getFallback helper (replaces direct fallbacks[currentSubject] usage) ──
function getFallback(subject, difficulty) {
  const subjectBank = fallbacks[subject] || fallbacks.algebra;
  // Support both flat arrays (old format) and difficulty-keyed objects (new format)
  if (Array.isArray(subjectBank)) return subjectBank;
  const pool = subjectBank[difficulty] || subjectBank.medium || Object.values(subjectBank)[0];
  return pool;
}