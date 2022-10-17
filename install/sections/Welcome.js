import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import Features from '../components/Features.js'
import SlimsButton from '../components/Button.js'

class Particle {
    constructor(effect, x, y, color) {
        this.effect = effect
        this.color = color
        this.x = (Math.round(Math.random()) * 2 - 1) * Math.random() * this.effect.width + x
        this.y = (Math.round(Math.random()) * 2 - 1) * Math.random() * this.effect.height + y
        this.originX = Math.floor(x)
        this.originY = Math.floor(y)
        this.size = this.effect.gap-1
        this.vx = 0
        this.vy = 0
        this.easy = 0.05
        this.friction = 0.8
    }
    draw(context) {
        context.fillStyle = this.color
        context.fillRect(this.x, this.y, this.size, this.size)
    }
    update() {
        this.dx = this.effect.mouse.x - this.x
        this.dy = this.effect.mouse.y - this.y
        this.distance = this.dx*this.dx + this.dy*this.dy
        this.force = -this.effect.mouse.radius / this.distance

        if (this.distance < this.effect.mouse.radius) {
            this.angel = Math.atan2(this.dy, this.dx)
            this.vx += this.force * Math.cos(this.angel)
            this.vy += this.force * Math.sin(this.angel)
        }

        this.x += (this.vx *= this.friction) + (this.originX - this.x) * this.easy
        this.y += (this.vy *= this.friction) + (this.originY - this.y) * this.easy
    }
}

class Effect {
    constructor(width, height) {
        this.width = width
        this.height = height
        this.particles = []
        this.gap = 3
        this.mouse = {
            x: undefined,
            y: undefined,
            radius: 300
        }
        window.addEventListener('mousemove', e => {
            this.mouse.x = e.x
            this.mouse.y = e.y
        })
    }
    init(context) {
        this.context = context

        const logo = document.getElementById('logo')

        const centerX = this.width * 0.5
        const centerY = this.height * 0.5

        // draw image
        const logoX = centerX - logo.width * 0.5
        const logoY = centerY - logo.height * 1.4
        context.drawImage(logo, logoX,logoY)

        // draw text
        context.font = '32px Arial'
        const welcome = ['Welcome to SL', 'i', 'MS']
        const welcomeStr = welcome.join('')
        let textWidth = context.measureText(welcomeStr).width
        let textStart = centerX - textWidth * 0.5
        let x = 0
        welcome.forEach(c => {
            context.fillStyle = c === 'i' ? 'rgb(245, 158, 11)' : 'white'
            context.fillText(c, textStart + x, centerY)
            x += context.measureText(c).width
        })

        // get pixels
        const pixels = this.context.getImageData(0, 0, this.width, this.height).data
        for (let y = 0; y < this.height; y += this.gap) {
            for (let x = 0; x < this.width; x += this.gap) {
                const index = (y * this.width + x) * 4
                const red = pixels[index]
                const green = pixels[index + 1]
                const blue = pixels[index + 2]
                const alpha = pixels[index + 3]
                const rgb = `rgb(${red}, ${green}, ${blue})`

                if (alpha > 0) this.particles.push(new Particle(this, x, y, rgb))
            }
        }
    }
    draw() {
        // draw particles
        this.particles.forEach(particle => particle.draw(this.context))
    }
    update() {
        this.particles.forEach(particle => particle.update())
    }
}

export default {
    components: {
        Logo,
        SlimsText,
        Features,
        SlimsButton
    },
    data() {
        return {
            effect: undefined,
            runAnimation: true
        }
    },
    beforeDestroy() {
        this.runAnimation = false
    },
    methods: {
        animate() {
            if (!this.runAnimation) return;
            this.effect.context.clearRect(0,0, this.effect.width, this.effect.height)
            this.effect.draw()
            this.effect.update()
            requestAnimationFrame(this.animate)
        },
        onLogoLoaded() {
            const canvas = document.getElementById('welcome')
            const ctx = canvas.getContext('2d')
            canvas.width = window.innerWidth * 0.5
            canvas.height = window.innerHeight
            this.effect = new Effect(canvas.width, canvas.height)
            this.effect.init(ctx)

            this.animate()
        }
    },
    template: `<section class="flex h-screen">
        <div class="w-1/2 p-4 flex flex-col">
            <div class="flex items-center hidden">
                <div class="w-12 mr-4">
                    <logo/>
                </div>
                <slims-text class="text-2xl text-gray-200" style="letter-spacing: 0.5em"/>
            </div>
            <div class="flex-1 flex flex-col justify-center items-center text-center text-gray-200 px-12">
                <canvas id="welcome" class="absolute left-0 top-0 z-0"></canvas>
                <img id="logo" class="hidden" @load="onLogoLoaded" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAAB4CAYAAAGGD+ySAAAAAXNSR0IArs4c6QAAAKJlWElmTU0AKgAAAAgABQESAAMAAAABAAEAAAEaAAUAAAABAAAASgEbAAUAAAABAAAAUgExAAIAAAAeAAAAWodpAAQAAAABAAAAeAAAAAAAAABIAAAAAQAAAEgAAAABQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykAAAOgAQADAAAAAQABAACgAgAEAAAAAQAAAGmgAwAEAAAAAQAAAHgAAAAAyLewqQAAAAlwSFlzAAALEwAACxMBAJqcGAAABHNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDYuMC4wIj4KICAgPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgICAgICAgICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgICAgICAgICB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIKICAgICAgICAgICAgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIj4KICAgICAgICAgPHRpZmY6WVJlc29sdXRpb24+NzI8L3RpZmY6WVJlc29sdXRpb24+CiAgICAgICAgIDx0aWZmOlhSZXNvbHV0aW9uPjcyPC90aWZmOlhSZXNvbHV0aW9uPgogICAgICAgICA8dGlmZjpPcmllbnRhdGlvbj4xPC90aWZmOk9yaWVudGF0aW9uPgogICAgICAgICA8eG1wTU06RGVyaXZlZEZyb20gcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjQzNjFGOURDOUNFNjExRTY4RkU5RDQzOTk5QTJGRTBFPC9zdFJlZjppbnN0YW5jZUlEPgogICAgICAgICAgICA8c3RSZWY6ZG9jdW1lbnRJRD54bXAuZGlkOjQzNjFGOUREOUNFNjExRTY4RkU5RDQzOTk5QTJGRTBFPC9zdFJlZjpkb2N1bWVudElEPgogICAgICAgICA8L3htcE1NOkRlcml2ZWRGcm9tPgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD54bXAuZGlkOjQzNjFGOURGOUNFNjExRTY4RkU5RDQzOTk5QTJGRTBFPC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06SW5zdGFuY2VJRD54bXAuaWlkOjQzNjFGOURFOUNFNjExRTY4RkU5RDQzOTk5QTJGRTBFPC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDUzYgKFdpbmRvd3MpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgoyY9k1AAAlk0lEQVR4Ae2dCZydVXnwZ8m+sRMwZjFsnw1UFFTUmgIiWLWfQEErILQfbpUCQltQLBatsoitsphWWtkqKlFQEUWJCCKKIktYA0kQkhAgYcs2mWyT9P8/Oc/93nvnzsy9M3cmE/D9/d455z3Lc571nOc877nvNDU16tq0aVPL0qVLV2d4zeTbLOsW/vr16zd11WDdunVldQnSc88994Znn332wy+++OKamTNntlZ2ZsTWZcuWrWH0o1944YU/SfU8rDDz/PPPT4gOxZGL+ainz/wh8VBMhw4d2gwGB7e0tLzVfLEu5zdV7WjlLrvs8guSXzhitc7dc4uedpJG0GsXYFyljjvuuOPiSs5Fo+bm5o6dd9555EsvvbRtlJU6WjBs2DChtyxZsiTkGO06pZ1oBPpGWo1CROtAc+i2225bjTlNjvg8o5QqFyxYsB30fGqnnXYaZidG/zwcHh1D5rbL4rlp9erVm9asWVOmHaVKMu3t7ZtWrVpVvZ6RTnnqqadeXexgHq2ZSt3fVJZ3el67di0YbUa/K0536hQF0LYg8j2mND7u6aeffq0NQe31PB/ZbSdRq9agEs0h4o+cVtH48hUrVnysWqfly5eftnLlyp3g5pNozZhSG1A5ywfZXCok09bWVvZsXSctGTlypCrm6G0zZswYM3r06JIiBLBOnaxAvYQ+yrwjVXYsU2QbVV52UPhwcXxlXXqu5FKxEeheGM9lI2kyQD3qmWee2TzZRKuKtBNNsPR7tlFmw4cP78QE65xQ324mLnBfA/da7XDrrbcO4Xlt1JnyfFh6doKhc7dWzHS4qdPU56RKx7LJJUYQIKOX0R91pZRGHT4IvVTYm4z0AWxpqBW6uAkxLASDTszsDfzURyxh2i1333330GpALAeHX7OW1EcNWA4F8J103lANcK1l9ofqXwivUx9ZwiB/kCUubjQ+K2N8HfmaMKbdRgb4ZpGt5ilfXJpEeUgraGAQg8VzMWUiFanZYgzgOcwvNSEiDMaZV5dwY5GR6smTJzNmzWMlnLvX5SJZhfz++++/nsXqT7bZZpuS2UJpB/c1XSlS6l7JRjrsWrdmFRCBWqeIFa6gheLExk6UgfEzO+ywQwljBwaBX3SLcQGq6zv318eNG9eJxz3KLAYG46RZo0aNetWYMWNKyBTG6THbibJqPRwI6v6TuvExUJ7b1sOyi62v1q9TGQ1bAfQIy2Ensp27AbaONt0iRb2LxKZYYYB34RNPPJGcMM2Fut9X82YTMjTWnq6rVU5Bge0B/JNeKZgU0XlV5RofwCPVFQO5F+RSlFVL6xK0rGG1fhagN+Fmvxtfb9uJEydWXZOqDVZXmZgznZ0EtSeQ71aOlYDraowc1zPAn3EfST4tkpUA+/S8YcOGTbFYFgEpw07rfLFBLfmsTXOrmUFl/6zS99elsbClGVU9FDm0ccumHpXGNmjfO3Oft9fSR0dfg7uvLuwyiZkLc7rlglsTZaHhKo/e8N4+7HHTjGNeOyuxGexvELgF55xzjobaEdRkL2kBrOhyfsue1bPVlEWYwNvI/e0m5yuupOamDkSaHBFSef+nlLWRHi4idvayLQh+kLrVpHtuLu3813ZpIxcTY3QuDhTdMu9/FWyxXPeNtjcH9dG2WlrzQMXOIiYyxbKe8g5UYkVPjaPefQRbAqciFWdWLRTZt+6BYkBW5NaOjo5vMLEuZ8C9orzLtBYZddmZCoTt0v9MV1pnX1nXpdp2B7xYh5Oiy72rZdoOJtG0/fbbd55NKikSMzB8oFbeFwc1L4WwkuT/T12dlEE3Cu+nhfTkSZMmLWPA2uatwmj33HNPZ2qo76QMDLIJv++XCxcu1NmYgS9ujKbPV5cy0jUG+j6O4BwIi5ugtiq2tWDRiaJqnYYMGdLsILBS+V3bG/nVNFAMjoB3h7VHIL8VCDxpWtT1lNY8UJ7y7yKiN/K2224bw6Bze3LHioMPUe+7u1RVtjAp6lfRbqzPercozAYQGKYiVbRJj6UxnLdYBm4By7Q85DXmiwxiuGxktc7FMmb1bZCdgdvP4P+NyHY0jOc7YsZPrHPeApvLqFhOxT7Tpk1r53kUWI4m7dFJZCZYjknIzkk4mcaomxjsJfqed/HFF3f2YmmQ5q3SZriIdo1598Ag/LiwaurivNWtk1EBReTsU1Fc22OW1XrYMKMahpkDV0BBXQth1dEBZoDpCyjLWgZMmmZDYrI7UK4CnGmbqp17WwjQxJ68nPe4YevtODX3g8I0JxI2nKahg2Cbxk26WhcO7kytGdiWbBhzLYjPVAlJ9R2fFSf1JqdJnJS/xL0hE3xFsY35LX6hj6/K24N2rYnnT4pUT/oY9RB3liqG9NY4IarTA0pUcBxELtHzIDXaYdR2KHfNa081pOnfzNuIUcKEMawOayX0Ats6g1Tr0+syB/MdEINt4F6XBzu/XwbLWDJmIoLxLgrmQWD7ww8/PKxXzKNT6P1nVAu5BnDTjRo5+RsdO2wo49HwJOBDzC2uRKo47wAdf01W89MdNPAtIpBUJipA/CWJcKbimuSmBefL/d1G9hKfHTt2bDMBm+8KFN9ouUQixdcUAfY1j139HycQ4TsO67YhyWY8mdbtttuu2bU9v1p9vQ49+K6D8KWOW1JTCi+E+jaXWIIOE4tIQVgLgxhM7+A+i+dS5J18SPR56/NM9g37U1eTDYQ06P/tvONwxny6Hhi2pc8eecZtA98zLVihmllZeXVHVLFtgcAzYyaDWapMWYQ5+jDmLlmV0ozJ88nWBZxoV2+a7W9en2asGBQVSX4L++sL8j57FAOMQn2eVZ1VIxD/npMMqTuk+bNnzx6q160q0e8SYQWcgNvbtCY1qRc4yIm4/u9wVZFnZ86vkA9QzQceeKD5UkFUNCJtiKS6Q0SCrEcap40YMUKptEDcVNTP9agtS/EU23QZTLeyjqvfiaqGy/jx45cYr2AmG6v6QeQBThRIz8X8GfuUZrJqAHoo2yJEBU4hRYg8xjfySpGyAyWQrdwKpciS8de2h/Caca25oYAx7H6xAWHHhfQek0BCL9soxdbW1hNdbtizuqzMiXbdpjTsckqPjvPmzRtOOzdOyU3CFvrHJ4sBK1LdI8b8ErdexbZIrWq4p64pfY899liLaowA+CgPO2zcuDFFilGR1RCq+9E7n6wC+a4ec6ClKiHV+tSlfgcddFCaybCBf8oz2TDcp0mohlviNJORdumTVUOgP8rqIqoaArvuuutzvvmNmYw2+4ZPhhSX2KcvM1m1MXsq6zNRMUDMZBB3fJ7JDExNh7AO1dSZDCkea/t6ZrKAX0/aMKJiUBBOXgoEPEn+UY5tudj+OK9HH3IbgePsTPaYfcKpjf6NSCWKMXs/U9M3GTBE/N8sjeUaNpPJ+5hcmjkX0IINHiGyPL+L7UsLYUS3MMfrCxqetx+EvtM2Ac98vVemY1ML68BEuKi3XvPWPLhLn/uYYje6geM6Q2kY2vdQHYTcL1KoYBnH4hnCfudManv7MeGcZ2AVWG4/fmPfGMd8tUsGcI+Eoevos47Jq4k1LUX7S+3ZbI0XMA2qBlGof2v2tNvcYrDav8POAi8B6UMm4AD3vUoPPNpcewi1vYn8udyJQaRnOT6pa+dGpDwu+nY5PA2SXUDENXkm08v2fsBOUd8lgAZVxDiMO5fbo0cbVG3w8shL3/EA4F4SCPA0k5GekAE3dLIJT53xPq60IKBdN2nx4sVlu/IG8W0zGLiXtvQQdbODMbiEzre2JxvoCpGCRBYBy7eOwry+LzC7GqvHcpBJtgQ33xK2lm3hL+wc9ZWAKE/Spd+RYTtZrf60u36VcAbkOSQFl+91wmFmVJK/y4iGhO/HsFPIi3a/KtYNCJJ9GSSOVEDUXyoN1dQZi2jPwZmQhsyYfcGxV31Rs1ZVDYncwf1LgQy0H9grxCs7FVTwx/kdn0bfkeNyM20fbSr79vW5odNwGP7kyZP/n6rGdTCeQxOu0lTcoWnmudKCOmXKlA/4EH3MD7qLGWxqnoLDE/+ISIo0d0RxT5VY1xxdKuzrVY0kpM8SAtHwwhcigcdFjvTn+m+c+b0yP3ssNwU48e1mWEf5r5UY92LUca7t+ksNhd3jFYTA6StyzFyXKf2kJ6TRHRDatEgAfTwcuN41ifRS+wTs7vo3rE5EBAYhR8QCqXOJ6rzBcuprnpKjLetTmUMM7PQjjqgXbq1X3SrHrLUt64mvUb7NgI5znjEJtgtVtxPdIQKMBOCBBx64K/+k5SJhMoHcgLT8HcPo3hDV3ZipLnSbQWa72usJkL/TyqjrEUgNDQIWsO8LjwNp/dauEJY8jhrAdN0EIGlmAuj5+m1wzDeGG9jL9A/nCqj4iofxZNwavQzyZ1sdOBWa9pwNMcOlt2UHdJ1TLKr25gy0ZjvpebTqLcAhmQOEHJg1Yl221f26w6ETYgLiyF2rrxzpOBQ99xd0Tq/rePU4DCKnEep6pDoajSmVodoXxLyBH73cw5jriF8Mw7aaWOc6qFtFJMroq1GotBzEyKVJgcqko0jhZiKs6+g8kkYPUb6c4IeinsGpvSZiFA+iBv0Wn8tIqmovkb+LmVRcrzSMZiyD/ALubXSpIPiHVobtmRfREO0n8zTcrkFiJztbD+CV6rB5L55dCDdmv+xay8oAWtCLK2AA+wa9DZCVqD8IChzT4h1gmZgmutnEtlNchPRj1iUvn4fxbrdJY7v9fiuDUICWEWQdZfECOPWhb6+DjTEOBHw4M3S13OfAxyTHqnZFH/D42+gjg4GRVCltukDqm3au5EY1giIWQN1JIdW8qavriKXjEe2Zkj3y1R5EFcmMR8kcfK52FaR6vVrlJbeNdl7s4cRqnajrJKFoVwD4U5FywYVLj1ofddG2mAbTYOKTwE8xBfp9v6d+RRjFPPA8UPkdx/etm3UtuPPpbUSxYU/5fMzXF2nvYeIYwjZhFcB3c3pllvw3+wfyxTwD/4f+H2O/mvtFD4zgaRxlm4Bpvo7Ll96JFt36OvpVb/r+97/fqXMj4eCxEPX2zKQTszoeFL0g5N2WcR1jG9L9mH497cVj+fQbfepNe9TTWgGCUOLMnDlz7s5+2b9DnN1vRB1WoVKeiLyO27IvOAXDgId84Oo7VzfD6Xw8PZf3OokXaex7Pi9hXA9xHklXaQhO7BAPe2D8FzlAo6RSRLZhEioCNR+2gErtz6Nn95txoza4pkFgTYcShVPv1W8EBSKqIqe0Vvj6hTcQR/Js1WeyX5beJCC9VBh9+pL2O0EiJ1GmvIK5ye03BFyTbelubOtF67mSZ2+7vlwDQlAgCOJx5OYTTgoQsYi67RoZ2x5QgioJQ2KvYRqfkqV1qFM669fxtqOsV7j1qlMg1oiUrcgiT0Ry/X2Gd4leBy7RxN7A3+IEQUharJjmr872NYsyP4ajW/SERCGtMm+7O0K3OEGBXEzzzIZH6gpR/hL3xBwim2G7WgiriSA55jUQgXbG0o3y4Mf2pPs7Nvex2hcz4qEQ1e0U3yNBABuNa56ObnLY4hAJ6wmobfp4pWkeoh7Mu9Rz2Z4I8r/Bp1s3qUeCAMpuvOU8iJCQH6LXvtTt96iP2IN8iheghl/0B2FcPa5VPRIkFGLU5+qDcd2PsbZwr8RzbnhczrH6etVEUBgs0nozjuU49BlHeuPr9cv4Ie2nRQLp9ci9viJbS/+aCApA6i8bsVWogOGjw7lVizP1y5DYvrYbAPsKdKqmdREkBIkyRVqz8rpxtfbF9Vvsy1jeFpVW3QSJsBeEhV92cvbLFlA2juiL3/LpHC/b3K3f//aaoMCsQNjuzIYT8y71YNeNyZMn/63tIkoUffoz7TNBReQ40/20nwyCsL+zHFX8qp709OnTJxXb9We+oQQhreSXPfnkk9/K9jXLN3OEj/+AGi7IRNbsl/WG8IYSVIkABK6ljCRFeJJfBmGX9SdhDSUIFUvwsJ2jtCEQfy9BdlVvL+59JYzrA9YxI77Xhzh1Yr4RV0MJgvs7GxLmulzksKUzfGuAU/s4a9fDzoaUfY56q79D+00nnniiAZSGXRE57TXACPnC8UdB9hnsRVi3a0PY0td9MBCJdJJfRtlXsht1F22aIPgF+s62XcAy3+vLIDcAFwsAztX8HpO2ybjpe5GGD7c9F7rKLUYt0zT9jdr6Y38PvhPhSj/AOk88qm1Tcn16L2WbuAJnxn7ena7B+nkEzdfql5H/ZxvSqEu/jLpkCPQ5JPdpczCe35L7pvoYsLs0YEHQQRWw3lYJq5KgwBGcv5DrZGiStF8pSMfAKEgHWnmueuZAA678oa4AHbwaV7sjplgHcknajPtvOV4ncm2qIHXJzisJkoG5LB32pe+BJZgBkEaX5RMdnhFIOyo5EfUM8mtfHmc1vVcADdH7jEnAYuyHcXbThxfA6edWZwkmlQOPVdzrM/Fftj4YWqYeIo7xesTlCdpMYVby0zY3kL8dD+DLzGBrqR+OMRMM3W6ZgPrr8pUok8cStitriAqNSPaBNYBTC5JpIgr7CGGwaTIhtjfiUkZQETmkMIkY9AIIWkP5UIC3sn4ciJf9SwiHru63wkVYvckzhjOwh9bfBQNvYmzBbACnITzvTCDlubrgCtAOAExn3xB9+oUzz7vXBagPjXk5PE1Vc+y8GB8tuMCtGuguF1a5YwfiZf8DR5TkTYjfonkQ9ZQZACdjNt+oK2yBMZaiVg8oGda3meKAdnzfcQK3Po0J8mkqZ5wXHSvHy64SaCMIiwkB2N/NRwZUt1gfG864xAwQTzbHQPvm2TB+05rej1LfpcS74mb0Qa0+pFqRrs4E7dFVn4aXg0RI69N5Ol3jKS3WqB3rHYwpuuwLHTArxbhr8TbqHavH9hCWXCWQuMP1SaLg8IN2DBWqBiTq6DcXgvzIkv1+1lO/arD6pQzCfNdT+p2PKzgIpi9gh5E7MG2SLUDIpVll/U2TRwHS+e5+Qa63QCXKviyI7wi/LKfTAyZEHpan3+T/0fYA66JvtBtUaUEKX9E1QQL6Zcu52yEI4SWv+l8zIV06wYOKKJEp2MkcAyX590j3FOsGHdJbAiE9/mCW46O2f4n0H1GVne51amVeVvd7eD448LTfoFbvQLQiTbZcUTboHrNpupN397INXtG/UHYajngTs77BpRY2AEPZEKyk/FbaHcbpzuE8ryPvR22GcmZQB/cCzPhcv4JMO/0Aq1N0YNARXUCoboel0LffsjCwuWgtLNvTWYbv1joY1J/WfgyXVddVHB6E0YfqwhJfGsd9FPeIHI45nPrHbEcIR1fgdDYfq51vgfdLBP5GAXgNZisbNEKC8SW3gU+njeAkxmddzGQobwJ+Sv3evhFgL+M28z/Ib6Mg2Fu86aGHHrpjM6uTaaQtoc+++0Fg+yhA4OxEv6uwQn/Y5Kr+RvK/ytPiKsb7B7okfuhQik/AfMWmMKHMWnSJEMqvFIpOL/k2HV/XGrT+IZ7fHczqjdYznjHB0h4LeEdxz8/w0xZGhzn7mz/Dw3ldcbzIv+xTtdNbQk1Z1M+EUekQMqmOODxqj13Jf3L8Z6dgSpHBUdaXtAjPf67E2Fd51j0faW9HMTZkARq5PjnGEm/veH5ZpMW1hTXAL6T/TEHkbZ5fRE/WQvnj3CnOI+EykXtApmPHcbxgOHicwP2UQiL1ToECBQi+3+cufcW9SF/0b3TacCZUzufM9R9j6nhBglkD7oUZ0/Oa4MJ/HfekvOjvRhztB0Eg68cG7hRfi7L+Sh3H8QI+eHyLtezV4kWZEZyfWAfu3odxP5oF+DT0fcg6L8oHTLE2j1jH36I2oXV7cF/vniWHqfzaU/pvCqT+hOdvAvSgJiojCY5aWWl6Q+E+AQ3PZSF18NyuhTkzYGHfLP7aRvqC1r6kvbKkjHgJAbTpWAUg4mjkXOrfI1KE3E1+yr0neV3kCWjpNRZ6qb1q8eanwflX/LjTXgq6mhctWvRf0LGTVsaM4Dvy28gn5Kk/Co9zQRagpxz+Kqiirv+tzEFiQD+Rggb9Tyy0CMhF318pOX93+J6c+gfIvz362L/RL/gD9kCn0FK2hkGr/z1mrnQjGD8K6Ru4DZSvcf1lLbb863iw6UeC4lvkZ0/4d2lJlYgwyBEM/LhagrYsRIP+ynf8ri9cwvH2nEyLrwap938HzsrBO4X3pVNOOWUsZSKY/tOf+a3lKjIVZu+AZ3qJWwSFAK0eT5vKb1elzWjGqZT5o+D0yQL5RNlxzCZLspU9Qv93Be09bSnKhNQdIgC8mnuygGm3HCTO9h+FIYRxFLVzb/QrnAjtH/1BPNchlN1L6sFzhebPN5e5eUTgv997771LVtYTkvQb8KsyRgjOh3I/lJV0KTw43ulc+rjuJH2zUyBT4Tg20V/ndOhs8u+UF/7omTbnwRsFaJ/dSb8XCkyY6gKsUD4mBaauNGtZVrYoiggmWkIEoCd0gci2bDgvFQADCtxwTezkh5sHyd+C7Nv8MRgb1DEUXUy0wH9aZJ99SH/mxpUx14Lk2ZysSf0oL8NJWAN1+UKF8RPXjz322DHgdR4zwEaZyeVb0z1JjXqsgYbz2V8NVwjQeYhCCTypc6011hjr7QbW4gsQ2iijJMA4mLb3Ud9JgbGw3yOD6QGLti1NmOw4EJmPQFxH/BAByp7eUbuhO9sPf9rBxtwlLyd1phygE+izkrsjh1jS/42lfvNqurlvWXSBcZzDZ9veKYN8m1EGhUb+dqaT9P2APG6/Ra7BUYaV8ITeP+e+S7xcR8TLdUa8wNnjxCXrt5/9xbHeq6gMwBzDfT73hjxOkgGwHf/3yXMmM1+EMrN0kUtS7G4aAkaaKmsRUiUR9C2ZM97S9ox5CXDS/2Qm79f0mFXSRnIlzDkjxqrcg1XCreUZWKUYIfnhCOUcx8wKto5neJUs3IX/AuqcBdI0VHx1WctYtbQBhzIFZrx3g8ML3Mmt5/kuT5b4P7Y6RIx8msLoWGJiVwPRptdCKsIUTtKWXAhjdFAeU0giSr6tEFO7CYVKv8S0eXdKFGPIBO6StaAMbwTubU5hlTFCxrqf8Q+LvrXAj7aNSMEz8R08ZkZ4jPxcGS0RsQA2Yqy6YDhvFw9gMW//iHsvF2EA+WWqa5mK1WThHsh6MFsBwsznp0yZ8hH6pwrqW7U0G5n3Nv+5z32uGcGcCrHpm9KU/wZ4B5C6ttjkChiyY170X8fYt1joJV4Bf3PJwP11vcpXc7KGeBoMKciVwjMsxktYlD/q/yT235HB2I+A42LxpN0OMPvLhcjGTL7n/hrrWEdei7X8yFNMp59+egftPk/7sRIOjCdpcoznmoHv11tPmzBhQjoiad/i+D4PhmvQCanIFBhWFlNj/boWTU+xPpi9G21v1CK8aPsXJPOctokR+iLwEAToP0ZbR/n1rDkRI9wDGB7vTFe2lvDConhQpYNaSJWcqpgWn8TzPMbfy7pf4zqN9s/5FjYEZ3/yzhvNuMo+bpXXViWkIocRysb4OAflzQsWLLgcC9nZtQVL2puym40AIBydhiOY8hbmtWw+69P7AlZ2DgY1HwY1csHIGtJNRSvbZZddHmW9eZ/rGJYmjWdjUSuEg3CnkFwT3hMb1ot4ubg9ZemiXY+ebbQdqPTlIqQyfiGI0m+ErMDALuZO/26EugO4DeN4OEWhnUBUJL16wGN8EIfjnQFMKxsMQeGXpZCCyaYIwQBo6XAKFnYfHuM7DOdw1MsPGF3A9Gjcx5eQ/kTvB3nP2DF58uQvnnTSSaOFQ/kWCwq/7IUkg4sXQinG1NYjtPOwspE5pnYIgppNG2NqhsFOInaZ9lfstX7LpyneGrAGcqP7ihNSMNkUYZRZGd7ibxDaAQqMja4fxvkazsZGrQzHwxd8P8/ho3bWMmOUOiVaWVlc07JGXq9oIVUyUm9RwVmOsFbhMX4GK2vNn3U7HEE9Rr1rme9iziBctdbwEuvYLVja6wNeo62s34T0yCOPlOIagfzWlCqsosfIOjaL3xGWDlpiPVcZVtLKuPwZxZ26+Lj3y7GyU4JW6vvMhz4LCWK8OkCqGc0S6X8A0VOYv93p+1yKqQXiW2MKjaVwFZ8JexELO8V/0GV4Ccs6DjoXSBdrmu/Xvsi06A/fVhAQLr2N7i3dvRYSSDtXN4PoU6Qf5Xkh3pJIbs/z+YWfhlzHNLJ7IOhUQL7P2hXwtkQq7UWh8Ub6h1jannkj/e/+WCArbDtpr3kctPUJAIgmWwfB67mniCQmPwVB/QBBpTFo49e/5uSpIM6ppUrK+/8ETVDaj6kC83vhDgHtpTgg5U0IrM8j90lIMbpIRt4vBrLoHu9XA/2aBpfzc/rRO+l47kvjnBrTwVVErF8dfbOVxeMf08yBhgipyE2EUjYV4CFdxfy9q1aGlvkvWmbZntT7CDaSpXNqnN87MmC9XKws6OlL2nAhVSJT9JBYv+ZhZUdqYUavaftphLEs95lEeqXvgHydj0v7NcI0pd9HK7RK2K+U534XUpGRWE4ppgbTfeE2AyvbTiuj3Rspa8g5teKYL4f8gAqpyDAEVrkPeQCBHapLy79G9oTSuWweVyM4p8Vuz6n1xwGRIq5bOr/FhFRJeLay5Bntt99+nlP7EkIbbYgGYR1E+632oGUlrfU+DxohFRGvtDKiF7/DxS87aInDsSFb2b70nWXkmvBMOmjJ8xY/aFmkp6/5QSmkSqKKMbXbb799DeGYf0VoQ42pYWVf5bVDKz8ecHPt56r+kXx7FtptnCfcP+BR3+sDjQFjS6RbhZBgbulA49FHH+0nrM5gj5UONCKU03jL2sEnAqRlDfdP2TjHvu1NCPGOvJFeiaWdTn2KdjTioOVACWxQCgmhlB1o5NjxfjD4ViPOWIkv6P6JeJkv7OST/0DrPXqIrmFY2AfCytiX/TX1f7ARbT2J+lnCVR2+Ouf4148R9DTrvBodud4MtTF/B42QtJY43AhpzTDwk9zphRsv3n5NfTofHgcaKdshC6bsQCPCKL2FZV92I4Lr6qDln9P2vmxlz7ORNv4Y4aoiLo3hdB+gVAopeVf8wmEgYmplZ6A90Ih2pwONajsMO4c7DjQ+AY3H+MqbzXA60EjYKTbBWklMb2WsKJbTr+ygJXUf5n4qd/CfMF0YBy2xWg9aTg1gAxGuQglb5s+fn+J/4s0dr0Fa/XThZUwPLZyLXo27ezLTySaOQv0Xv/2cEEgCoCG7fS2lsKfZNGXKlA9jLekQCBZyP+PpascP0/x3xROyteyJRdxgnZdRDPBOCrW5pOe/tucuCROhXQvdk4XPuqVAfgSdAciDluk8OvgtBs9jokJeyNB47ktaFD58n8JbhG/69oCp/IM8r2YmEPw30hh+LwFkbjQkQ2jdc9YN++l+ERHg7sb9vdBYxkw/es5TziLqPhREN5IZAbOrVKYXlMfPz34U3OJXeaUfL6vA1F2Nx1gKCouncCOl/sLsWfoCcAkzRAqDU+8662v2ksJLL20WoaC29U6fIlBQ5K/lH/ClvkkjOKf2AlpVOqfGgls8pzYJHK52sTWmRudL/TliEFwcNCNbhghvKT9In/RNBDR5Hnf6sgn9bH4j1rtbtpaJHCP+TsBV67nrspboW2/qOIWDlv7HjSuw3PHihVbvQ/0t3L6G0MqP4l6UFWs+Qjnc8agPK03ejGXSSP8kFOo38aOzXeHFf/trDg2CJpdxv8p23Etpc6qncY1rct7iWCx4hXA6XTQuWytAYn8Ah2elkLr8nIzAYPQENCR9XUREaN/O3ZGJWgq8j8egai/jNWTqCJiNTsGv9AsNYUPb6dCzLNNT+vGyVkb5F7gvj4OX5J/mPoF7jtYC7WmW0lIyb35EIPm1gXNx1oky05LUi4XFvEjy7KkaN4vDAPop0k/zMmsEGpH+DaK/CWXQJn/QjJvsT0rWkh+Olfips5+ggWcwvz4sXBEx7KNmFcfZGvLQLb/cMCcPEqbvx/OF8OIg6eeVuf9MMf0yskCP33tupc0Q+LWMSMnnmbm+Yj1tw1Hw+EFjLpEsShsNmY5m/U7cmHvVJH8pt0FzJn8vAi19qoy+Wk2PStEYTPsXinRIT4wiH7jnZB74eYB1WlacJIInb4i28m9A+ZCnq8R4EBmLwL4EgvGrwdJPGynzOw/nspZt8dOgwax606JQdAag56vcSRDQnX5CqrJSpjP0z7QfkDN5ddGhdlRY2btA1u9ex+9xSz9ehqjf4CG9NQYYcO2KgbtJK+lBwd4BPfcqiOxApR89Z8HcSd2fBbjBSE/gVpYWNY91aQeI+BrTX/o4RYXmtVF3VrQn7dfToGVIVjwwdmsc0McBGgleOgLpw4jMFMX/naHDcP7WPDNUkJ4WyDK3HGEdCfHztDIFR77NOVyvCGbM4rnfToMWkUMoZTFCGP8Wxr7DfU5eV5Mnm61lNvWHRv+txloC4XrTsBr76bLDmCtlSrjsCCo+COgX/E8N+Gp6Ib4XxXWlwuBObr954H+Ke1UWhGtn+h5QVp5LUJ7tYwDal5yFKHtFpDKsSDzamnbheR+SFmL3FfmLjj9EgHsFY/Ia2K3HCOyytRL4r+O+WavNe5s2YMaHEedSd0QRvvjF8x/TzIHM+PSEdU1Fs7+rkAwlkddz2pgF+Az5E4NxxY1x5bsi2p2MINKHEcn7NbF2rVbrRShXULdrwCkqTJT9Me2GAxUBWWNqH4fBfi5Fgcns1XHQEmZ/ixjkFMHxqbe9eP6+dQqYfDFGuJDn42JYhfJHawluNCAtWhku/DSE9GMtwqCwAuNO+xZSD8sXg5UzEczUQGFrs5Zu5/MgajCmMLoUUtESENonCLkcRwjGVyxPcF9JkPRycXcqdN3x/+35vLVd/wu5SxViaxeoQQAAAABJRU5ErkJggg==" alt="Logo">
                <p class="mt-48 mb-4">As an integrated library management system, <slims-text></slims-text> (Senayan Library Management System) offers many
                    features to assist libraries and librarians do their jobs quickly, neatly, and with style.</p>
                <slims-button class="z-10" @click="$emit('click')" text="Get Started"></slims-button>
            </div>
        </div>
        <div class="w-1/2 py-8">
            <div class="bg-gray-100 rounded-l-lg h-full py-4 px-16">
                <features></features>
            </div>
        </div>
    </section>`
}
