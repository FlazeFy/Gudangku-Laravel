<style>
    #room-container {
        height: 75vh;
        width: 100%;
        border: var(--spaceMini) solid var(--primaryColor);
        border-radius: var(--roundedLG);
    }
    #room-container canvas {
        border-radius: var(--roundedLG);
    }
</style>

<div id="room-container"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('room-container')
        const scene = new THREE.Scene()
        const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000)
        const renderer = new THREE.WebGLRenderer()

        renderer.setSize(container.clientWidth, container.clientHeight)
        container.appendChild(renderer.domElement)
        const light = new THREE.AmbientLight(0x404040)
        scene.add(light)

        const directionalLight = new THREE.DirectionalLight(0xffffff, 1)
        directionalLight.position.set(1, 1, 1).normalize()
        scene.add(directionalLight)
        camera.position.z = 5

        const controls = new THREE.OrbitControls(camera, renderer.domElement)
        controls.enableDamping = true
        controls.dampingFactor = 0.25
        controls.screenSpacePanning = false
        controls.maxPolarAngle = Math.PI / 2

        let loader = new THREE.TDSLoader()
        loader.load(`<?= asset('images/Room.3ds') ?>`, (object) => {
            scene.add(object)
        })

        const render = () => {
            requestAnimationFrame(render)
            controls.update()
            renderer.render(scene, camera)
        }
        render()

        window.addEventListener('resize', () => {
            camera.aspect = container.clientWidth / container.clientHeight
            camera.updateProjectionMatrix()
            renderer.setSize(container.clientWidth, container.clientHeight)
        })
    })
</script>